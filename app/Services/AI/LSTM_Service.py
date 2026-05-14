import numpy as np
import pandas as pd
import holidays

from datetime import timedelta
from typing import List

from fastapi import FastAPI
from pydantic import BaseModel

from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import LSTM, Dense
from tensorflow.keras.callbacks import EarlyStopping

from sklearn.preprocessing import MinMaxScaler
from sklearn.metrics import (
    mean_squared_error,
    mean_absolute_error,
    mean_absolute_percentage_error
)

app = FastAPI(
    title="LSTM Forecast Service",
    version="2.0.0"
)

# HEALTH CHECK
@app.get("/")
def health_check():
    return {
        "status": "healthy",
        "service": "Improved LSTM Forecast Service",
        "version": "2.0.0"
    }

# REQUEST MODELS
class DataPoint(BaseModel):
    date: str
    count: float


class RequestData(BaseModel):
    data: List[DataPoint]
    forecast_days: int = 7
    use_dummy_data: bool = False


# FEATURE ENGINEERING
def create_features(df):

    df['date'] = pd.to_datetime(df['date'])
    df = df.sort_values('date')

    # Calendar features
    df['day_of_week'] = df['date'].dt.dayofweek
    df['month'] = df['date'].dt.month

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
    df['rolling_7'] = (
        df['count']
        .rolling(window=7)
        .mean()
    )

    df['rolling_14'] = (
        df['count']
        .rolling(window=14)
        .mean()
    )

    # Remove NaN rows
    df = df.dropna().reset_index(drop=True)

    return df


# PREPROCESSING
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

    scaler = MinMaxScaler()

    scaled = scaler.fit_transform(features)

    return scaled, scaler


# CREATE SEQUENCES
def create_sequences(data, window=7):

    X = []
    y = []

    for i in range(len(data) - window):

        X.append(data[i:i + window])

        # target = count
        y.append(data[i + window][0])

    return np.array(X), np.array(y)


# BUILD MODEL
def build_model(input_shape):

    model = Sequential()

    model.add(
        LSTM(
            64,
            input_shape=input_shape
        )
    )

    model.add(Dense(1))

    model.compile(
        optimizer='adam',
        loss='mse'
    )

    return model


# CONFIDENCE INTERVAL
def confidence_interval(pred, rmse):

    lower = pred - (1.96 * rmse)
    upper = pred + (1.96 * rmse)

    return lower, upper


# FORECAST FUNCTION
def forecast(
    model,
    data,
    scaler,
    df,
    days=7,
    window=7
):

    results = []

    last_seq = data[-window:].copy()

    last_date = df['date'].max()

    id_holidays = holidays.ID()

    for i in range(days):

        pred_scaled = model.predict(
            last_seq.reshape(1, window, -1),
            verbose=0
        )[0][0]

        next_date = last_date + timedelta(days=i + 1)

        dow = next_date.dayofweek

        month = next_date.month

        is_weekend = int(dow in [5, 6])

        is_holiday = int(next_date in id_holidays)

        # Lag features
        lag_1 = pred_scaled

        lag_7 = (
            last_seq[-7][0]
            if len(last_seq) > 7
            else pred_scaled
        )

        # Rolling features
        rolling_7 = np.mean(
            [x[0] for x in last_seq[-7:]]
        )

        rolling_14 = (
            np.mean([x[0] for x in last_seq[-14:]])
            if len(last_seq) > 14
            else rolling_7
        )

        # Construct new row
        new_row = [
            pred_scaled,
            dow,
            month,
            is_weekend,
            is_holiday,
            lag_1,
            lag_7,
            rolling_7,
            rolling_14
        ]

        # Update rolling sequence
        last_seq = np.vstack([
            last_seq[1:],
            new_row
        ])

        # Inverse transform
        dummy = np.zeros((1, len(FEATURE_COLUMNS)))

        dummy[0][0] = pred_scaled

        inv = scaler.inverse_transform(dummy)[0][0]

        results.append({
            "date": next_date.strftime("%Y-%m-%d"),
            "predicted": float(max(0, inv))
        })

    return results


# DUMMY DATA GENERATOR
def generate_dummy_booking_data(days=180):

    import random
    from datetime import datetime

    data = []

    start_date = datetime.now() - timedelta(days=days)

    id_holidays = holidays.ID()

    for i in range(days):

        date = start_date + timedelta(days=i)

        dow = date.weekday()

        # Base demand
        count = 20

        # Weekday pattern
        if dow < 5:
            count += random.randint(5, 10)
        else:
            count += random.randint(-2, 5)

        # Holiday surge
        if date in id_holidays:
            count += random.randint(15, 30)

        # Monthly trend
        count += i * 0.03

        # Random noise
        count += random.randint(-4, 4)

        count = max(1, int(count))

        data.append({
            "date": date.strftime("%Y-%m-%d"),
            "count": float(count)
        })

    return data


# MAIN PREDICTION ENDPOINT
@app.post("/predict")
def predict(request: RequestData):

    # Use dummy data if requested
    if request.use_dummy_data:

        dummy_data = generate_dummy_booking_data(180)

        df = pd.DataFrame(dummy_data)

    else:

        df = pd.DataFrame([
            d.dict()
            for d in request.data
        ])

    # Validate minimum rows
    if len(df) < 45:

        return {
            "error": "Insufficient data",
            "message": (
                f"Need at least 45 data points, "
                f"got {len(df)}"
            ),
            "predictions": []
        }

    # Feature engineering
    df = create_features(df)

    # Preprocess
    scaled, scaler = preprocess(df)

    # Create sequences
    X, y = create_sequences(scaled)

    # Train/Test split
    split = int(len(X) * 0.8)

    X_train = X[:split]
    X_test = X[split:]

    y_train = y[:split]
    y_test = y[split:]

    # Build model
    model = build_model(
        (X.shape[1], X.shape[2])
    )

    # Early stopping
    early_stop = EarlyStopping(
        monitor='loss',
        patience=5,
        restore_best_weights=True
    )

    # Training
    model.fit(
        X_train,
        y_train,
        epochs=100,
        callbacks=[early_stop],
        verbose=0
    )

    # Evaluate
    preds = model.predict(
        X_test,
        verbose=0
    )

    rmse = np.sqrt(
        mean_squared_error(y_test, preds)
    )

    mae = mean_absolute_error(
        y_test,
        preds
    )

    mape = mean_absolute_percentage_error(
        y_test,
        preds
    )

    # Forecast
    future = forecast(
        model,
        scaled,
        scaler,
        df,
        request.forecast_days
    )

    # Confidence intervals
    final = []

    for item in future:

        lower, upper = confidence_interval(
            item["predicted"],
            rmse
        )

        final.append({
            "date": item["date"],
            "predicted": round(item["predicted"], 2),
            "lower_bound": round(float(lower), 2),
            "upper_bound": round(float(upper), 2),
            "confidence": round(
                float(max(0, 1 - rmse)),
                4
            )
        })

    return {
        "model": "Improved LSTM Forecast Model",
        "features_used": FEATURE_COLUMNS,
        "metrics": {
            "rmse": round(float(rmse), 4),
            "mae": round(float(mae), 4),
            "mape": round(float(mape), 4)
        },
        "rmse": round(float(rmse), 4),
        "predictions": final,
        "data_source": (
            "dummy"
            if request.use_dummy_data
            else "real"
        ),
        "training_samples": len(X_train),
        "test_samples": len(X_test)
    }


# 3-WEEK FORECAST ENDPOINT
@app.post("/predict-3weeks")
def predict_three_weeks(request: RequestData):

    request.forecast_days = 21

    if (
        not request.use_dummy_data
        and len(request.data) < 45
    ):
        request.use_dummy_data = True

    result = predict(request)

    if (
        "predictions" in result
        and result["predictions"]
    ):

        weekly_summary = []

        predictions = result["predictions"]

        for week_num in range(3):

            start_idx = week_num * 7
            end_idx = min(
                start_idx + 7,
                len(predictions)
            )

            week_data = predictions[start_idx:end_idx]

            if week_data:

                avg_predicted = sum(
                    p["predicted"]
                    for p in week_data
                ) / len(week_data)

                weekly_summary.append({
                    "week": week_num + 1,
                    "start_date": week_data[0]["date"],
                    "end_date": week_data[-1]["date"],
                    "avg_predicted": round(avg_predicted, 2),
                    "total_predicted": round(
                        sum(p["predicted"] for p in week_data),
                        2
                    )
                })

        result["weekly_summary"] = weekly_summary
        result["forecast_period"] = "3 weeks (21 days)"
        result["title"] = "LSTM Model Predictions (Based on Dummy Booking Counts) for the Following 3 Weeks"

    return result


# DEMO ENDPOINT
@app.get("/demo")
def demo_prediction():

    dummy_data = generate_dummy_booking_data(180)

    request = RequestData(
        data=[
            DataPoint(**d)
            for d in dummy_data
        ],
        forecast_days=21,
        use_dummy_data=True
    )

    result = predict_three_weeks(request)

    return {
        "title": "LSTM Model Predictions (Based on Dummy Booking Counts) for the Following 3 Weeks",
        "description": (
            "Uses holiday-aware forecasting, "
            "lag features, and rolling averages"
        ),
        "training_data_points": 180,
        "forecast_days": 21,
        **result
    }
