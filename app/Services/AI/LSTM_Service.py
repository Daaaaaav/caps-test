import numpy as np
import pandas as pd
import holidays
import os

from datetime import timedelta
from typing import List, Optional

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field

from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import LSTM, Dense, Dropout
from tensorflow.keras.callbacks import EarlyStopping
from tensorflow.keras.regularizers import l2

from sklearn.preprocessing import MinMaxScaler
from sklearn.metrics import (
    mean_squared_error,
    mean_absolute_error,
    mean_absolute_percentage_error
)

app = FastAPI(
    title="LSTM Forecast Service",
    version="2.1.0"
)

_raw_origins = os.getenv("ALLOWED_ORIGINS", "*")
_origins = [o.strip() for o in _raw_origins.split(",") if o.strip()]

app.add_middleware(
    CORSMiddleware,
    allow_origins=_origins,
    allow_origin_regex=r"https://.*\.ngrok-free\.app",  
    allow_credentials=True,
    allow_methods=["GET", "POST"],
    allow_headers=["*"],
)

# HEALTH CHECK
@app.get("/")
def health_check():
    return {
        "status":  "healthy",
        "service": "Improved LSTM Forecast Service",
        "version": "2.1.0"
    }


# ── CONFIG MODEL (sent by Laravel, all values from ai_settings table) ────────

class LSTMConfig(BaseModel):
    lstm_units:          int   = Field(default=64,      ge=8,   le=512)
    dropout_rate:        float = Field(default=0.1,     ge=0.0, le=0.5)
    l2_regularization:   float = Field(default=1e-5,    ge=0.0, le=0.1)
    sequence_window:     int   = Field(default=7,       ge=3,   le=60)
    epochs:              int   = Field(default=150,     ge=1,   le=1000)
    batch_size:          int   = Field(default=16,      ge=4,   le=256)
    validation_split:    float = Field(default=0.15,    ge=0.05, le=0.30)
    early_stop_patience: int   = Field(default=10,      ge=1,   le=50)
    min_data_points:     int   = Field(default=45,      ge=10)
    history_days:        int   = Field(default=730,     ge=30)
    confidence_min:      float = Field(default=0.30,    ge=0.0, le=1.0)
    confidence_max:      float = Field(default=0.92,    ge=0.0, le=1.0)


# ── REQUEST MODELS ───────────────────────────────────────────────────────────

class DataPoint(BaseModel):
    date: str
    count: float


class RequestData(BaseModel):
    data: List[DataPoint]
    forecast_days: int = 7
    use_dummy_data: bool = False
    lstm_config: Optional[LSTMConfig] = None

    def get_config(self) -> LSTMConfig:
        return self.lstm_config if self.lstm_config is not None else LSTMConfig()


# ── FEATURE ENGINEERING ──────────────────────────────────────────────────────

def create_features(df):

    df['date'] = pd.to_datetime(df['date'])
    df = df.sort_values('date')

    # Calendar features
    df['day_of_week'] = df['date'].dt.dayofweek
    df['month']       = df['date'].dt.month

    df['is_weekend'] = (
        df['day_of_week']
        .isin([5, 6])
        .astype(int)
    )

    # Retrieve Indonesian holidays
    id_holidays = holidays.ID()

    df['is_holiday'] = df['date'].apply(
        lambda x: 1 if x in id_holidays else 0
    )

    # Lag features
    df['lag_1'] = df['count'].shift(1)
    df['lag_7'] = df['count'].shift(7)

    # Rolling averages
    df['rolling_7']  = df['count'].rolling(window=7).mean()
    df['rolling_14'] = df['count'].rolling(window=14).mean()

    # Remove NaN rows
    df = df.dropna().reset_index(drop=True)

    return df


# ── PREPROCESSING ────────────────────────────────────────────────────────────

FEATURE_COLUMNS = [
    'count',
    'day_of_week',
    'month',
    'is_weekend',
    'is_holiday',
    'lag_1',
    'lag_7',
    'rolling_7',
    'rolling_14'
]


def preprocess(df):
    features = df[FEATURE_COLUMNS]
    scaler   = MinMaxScaler()
    scaled   = scaler.fit_transform(features)
    return scaled, scaler


# ── CREATE SEQUENCES (window from config) ────────────────────────────────────

def create_sequences(data, window: int = 7):
    X = []
    y = []

    for i in range(len(data) - window):
        X.append(data[i:i + window])
        y.append(data[i + window][0])  # target = count column

    return np.array(X), np.array(y)


# ── BUILD MODEL (uses dynamic config) ────────────────────────────────────────

def build_model(input_shape, cfg: LSTMConfig):
    model = Sequential()

    model.add(
        LSTM(
            cfg.lstm_units,
            input_shape=input_shape,
            kernel_regularizer=l2(cfg.l2_regularization),
            recurrent_regularizer=l2(cfg.l2_regularization),
        )
    )

    model.add(Dropout(cfg.dropout_rate))
    model.add(Dense(1))

    model.compile(optimizer='adam', loss='mse')
    return model


# ── CONFIDENCE SCORE ─────────────────────────────────────────────────────────

def compute_confidence(rmse: float, y_test: np.ndarray, cfg: LSTMConfig) -> float:
    data_range = float(np.max(y_test) - np.min(y_test))
    if data_range < 1e-6:
        return 0.5

    nrmse      = rmse / data_range
    confidence = max(cfg.confidence_min, min(cfg.confidence_max, 1.0 - nrmse))
    return round(confidence, 4)


# ── FORECAST FUNCTION ────────────────────────────────────────────────────────

def forecast(model, data, scaler, df, days: int = 7, window: int = 7):

    results     = []
    last_seq    = data[-window:].copy()
    last_date   = df['date'].max()
    id_holidays = holidays.ID()

    for i in range(days):

        pred_scaled = model.predict(
            last_seq.reshape(1, window, -1),
            verbose=0
        )[0][0]

        next_date  = last_date + timedelta(days=i + 1)
        dow        = next_date.dayofweek
        month      = next_date.month
        is_weekend = int(dow in [5, 6])
        is_holiday = int(next_date in id_holidays)

        lag_1    = pred_scaled
        lag_7    = last_seq[-7][0] if len(last_seq) > 7 else pred_scaled
        rolling_7  = np.mean([x[0] for x in last_seq[-7:]])
        rolling_14 = (
            np.mean([x[0] for x in last_seq[-14:]])
            if len(last_seq) > 14
            else rolling_7
        )

        new_row  = [pred_scaled, dow, month, is_weekend, is_holiday, lag_1, lag_7, rolling_7, rolling_14]
        last_seq = np.vstack([last_seq[1:], new_row])

        dummy       = np.zeros((1, len(FEATURE_COLUMNS)))
        dummy[0][0] = pred_scaled
        inv         = scaler.inverse_transform(dummy)[0][0]

        results.append({
            "date":      next_date.strftime("%Y-%m-%d"),
            "predicted": float(max(0, inv))
        })

    return results


# ── DUMMY DATA GENERATOR ─────────────────────────────────────────────────────

def generate_dummy_booking_data(days: int = 180):
    import random
    from datetime import datetime

    data        = []
    start_date  = datetime.now() - timedelta(days=days)
    id_holidays = holidays.ID()

    for i in range(days):
        date = start_date + timedelta(days=i)
        dow  = date.weekday()

        count = 20
        if dow < 5:
            count += random.randint(5, 10)
        else:
            count += random.randint(-2, 5)

        if date in id_holidays:
            count += random.randint(15, 30)

        count += i * 0.03
        count += random.randint(-4, 4)
        count  = max(1, int(count))

        data.append({
            "date":  date.strftime("%Y-%m-%d"),
            "count": float(count)
        })

    return data


# ── MAIN PREDICTION ENDPOINT ─────────────────────────────────────────────────

@app.post("/predict")
def predict(request: RequestData):

    cfg = request.get_config()

    # Use dummy data if requested
    if request.use_dummy_data:
        dummy_data = generate_dummy_booking_data(180)
        df = pd.DataFrame(dummy_data)
    else:
        df = pd.DataFrame([d.dict() for d in request.data])

    # Validate minimum rows (from config)
    if len(df) < cfg.min_data_points:
        return {
            "error":       "Insufficient data",
            "message":     (
                f"Need at least {cfg.min_data_points} data points, "
                f"got {len(df)}"
            ),
            "predictions": []
        }

    df             = create_features(df)
    scaled, scaler = preprocess(df)
    X, y           = create_sequences(scaled, window=cfg.sequence_window)

    split   = int(len(X) * 0.8)
    X_train = X[:split];  X_test  = X[split:]
    y_train = y[:split];  y_test  = y[split:]

    model = build_model((X.shape[1], X.shape[2]), cfg)

    early_stop = EarlyStopping(
        monitor='val_loss',
        patience=cfg.early_stop_patience,
        restore_best_weights=True,
        min_delta=1e-4,
    )

    model.fit(
        X_train, y_train,
        epochs=cfg.epochs,
        batch_size=cfg.batch_size,
        validation_split=cfg.validation_split,
        callbacks=[early_stop],
        verbose=0
    )

    preds = model.predict(X_test, verbose=0)

    rmse = np.sqrt(mean_squared_error(y_test, preds))
    mae  = mean_absolute_error(y_test, preds)
    mape = mean_absolute_percentage_error(y_test, preds)

    future = forecast(
        model, scaled, scaler, df,
        request.forecast_days,
        window=cfg.sequence_window
    )

    # Historical floor
    recent   = df['count'].tail(90)
    nonzero  = recent[recent > 0]
    hist_floor = float(nonzero.mean()) if len(nonzero) > 0 else 0.0

    confidence_score = compute_confidence(rmse, y_test, cfg)

    count_min   = scaler.data_min_[0]
    count_max   = scaler.data_max_[0]
    count_range = count_max - count_min if count_max > count_min else 1.0
    rmse_real   = float(rmse) * count_range

    final = []

    for item in future:
        raw_pred = item["predicted"]

        if raw_pred < hist_floor * 0.1 and hist_floor > 0:
            raw_pred = hist_floor * 0.5

        lower = max(0.0, raw_pred - 1.96 * rmse_real)
        upper = raw_pred + 1.96 * rmse_real

        final.append({
            "date":        item["date"],
            "predicted":   round(raw_pred, 2),
            "lower_bound": round(lower, 2),
            "upper_bound": round(upper, 2),
            "confidence":  confidence_score,
        })

    return {
        "model":            "Improved LSTM Forecast Model",
        "features_used":    FEATURE_COLUMNS,
        "config_used":      cfg.dict(),          # echo back what was used
        "metrics": {
            "rmse": round(float(rmse), 4),
            "mae":  round(float(mae),  4),
            "mape": round(float(mape), 4)
        },
        "rmse":             round(float(rmse), 4),
        "predictions":      final,
        "data_source":      "dummy" if request.use_dummy_data else "real",
        "training_samples": len(X_train),
        "test_samples":     len(X_test)
    }


# ── 3-WEEK FORECAST ENDPOINT ─────────────────────────────────────────────────

@app.post("/predict-3weeks")
def predict_three_weeks(request: RequestData):

    cfg = request.get_config()
    request.forecast_days = 21

    if (
        not request.use_dummy_data
        and len(request.data) < cfg.min_data_points
    ):
        pass  # proceed — will surface as an error in predict()

    result = predict(request)

    if "predictions" in result and result["predictions"]:
        weekly_summary = []
        predictions    = result["predictions"]

        for week_num in range(3):
            start_idx = week_num * 7
            end_idx   = min(start_idx + 7, len(predictions))
            week_data = predictions[start_idx:end_idx]

            if week_data:
                avg_predicted = sum(p["predicted"] for p in week_data) / len(week_data)
                weekly_summary.append({
                    "week":            week_num + 1,
                    "start_date":      week_data[0]["date"],
                    "end_date":        week_data[-1]["date"],
                    "avg_predicted":   round(avg_predicted, 2),
                    "total_predicted": round(sum(p["predicted"] for p in week_data), 2)
                })

        result["weekly_summary"]  = weekly_summary
        result["forecast_period"] = "3 weeks (21 days)"
        result["title"]           = "Booking Predictions for the Following 3 Weeks"

    return result


# ── DEMO ENDPOINT ─────────────────────────────────────────────────────────────

@app.get("/demo")
def demo_prediction():

    dummy_data = generate_dummy_booking_data(180)

    request = RequestData(
        data=[DataPoint(**d) for d in dummy_data],
        forecast_days=21,
        use_dummy_data=True
        # lstm_config intentionally omitted — defaults apply
    )

    result = predict_three_weeks(request)

    return {
        "title":               "LSTM Model Predictions (Based on Dummy Booking Counts) for the Following 3 Weeks",
        "description":         (
            "Uses holiday-aware forecasting, "
            "lag features, and rolling averages"
        ),
        "training_data_points": 180,
        "forecast_days":        21,
        **result
    }
