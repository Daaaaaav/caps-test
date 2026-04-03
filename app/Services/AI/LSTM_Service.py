import numpy as np
import pandas as pd
from datetime import timedelta

from fastapi import FastAPI
from pydantic import BaseModel
from typing import List

from tensorflow.keras.models import Sequential
from tensorflow.keras.layers import LSTM, Dense
from sklearn.preprocessing import MinMaxScaler
from sklearn.metrics import mean_squared_error

app = FastAPI()

@app.get("/")
def health_check():
    return {"status": "healthy", "service": "LSTM Prediction Service", "version": "1.0.0"}

class DataPoint(BaseModel):
    date: str
    count: float

class RequestData(BaseModel):
    data: List[DataPoint]
    forecast_days: int = 7
    use_dummy_data: bool = False  # Flag to use dummy data for demonstration


# Feature Engineering
def create_features(df):
    df['date'] = pd.to_datetime(df['date'])

    df['day_of_week'] = df['date'].dt.dayofweek
    df['is_weekend'] = df['day_of_week'].isin([5, 6]).astype(int)

    return df


# Preprocessing
def preprocess(df):
    features = df[['count', 'day_of_week', 'is_weekend']]

    scaler = MinMaxScaler()
    scaled = scaler.fit_transform(features)

    return scaled, scaler


# Sequence
# =========================
def create_sequences(data, window=7):
    X, y = [], []

    for i in range(len(data) - window):
        X.append(data[i:i+window])
        y.append(data[i+window][0])  # target = count

    return np.array(X), np.array(y)


# Model
def build_model(input_shape):
    model = Sequential()
    model.add(LSTM(64, input_shape=input_shape))
    model.add(Dense(1))
    model.compile(optimizer='adam', loss='mse')
    return model


# Calculate confidentce interval
def confidence_interval(pred, rmse):
    lower = pred - 1.96 * rmse
    upper = pred + 1.96 * rmse
    return lower, upper


# Model for forecast
def forecast(model, data, scaler, df, days=7, window=7):
    results = []

    last_seq = data[-window:]
    last_date = df['date'].max()

    for i in range(days):
        pred = model.predict(last_seq.reshape(1, window, -1), verbose=0)[0][0]

        # inverse scale (only count)
        dummy = np.zeros((1, 3))
        dummy[0][0] = pred
        inv = scaler.inverse_transform(dummy)[0][0]

        next_date = last_date + timedelta(days=i+1)
        dow = next_date.dayofweek
        is_weekend = int(dow in [5, 6])

        new_row = [pred, dow, is_weekend]
        last_seq = np.vstack([last_seq[1:], new_row])

        results.append({
            "date": next_date.strftime("%Y-%m-%d"),
            "predicted": float(inv)
        })

    return results


# Dummy data generation
def generate_dummy_booking_data(days=90):
    """
    Generate realistic dummy booking data for demonstration
    Simulates booking patterns with weekly seasonality
    """
    import random
    from datetime import datetime, timedelta
    
    data = []
    start_date = datetime.now() - timedelta(days=days)
    
    for i in range(days):
        date = start_date + timedelta(days=i)
        day_of_week = date.weekday()
        
        # Base booking count
        base_count = 8
        
        # Weekly pattern (higher on weekdays, lower on weekends)
        if day_of_week < 5:  # Monday-Friday
            count = base_count + random.randint(2, 6)
        else:  # Weekend
            count = base_count + random.randint(-2, 2)
        
        # Add some random variation
        count += random.randint(-2, 3)
        
        # Add monthly trend (slight increase over time)
        trend = i * 0.02
        count = int(count + trend)
        
        # Ensure non-negative
        count = max(1, count)
        
        data.append({
            'date': date.strftime('%Y-%m-%d'),
            'count': float(count)
        })
    
    return data


# Prediction API endpoint
@app.post("/predict")
def predict(request: RequestData):
    # Use dummy data if requested
    if request.use_dummy_data:
        dummy_data = generate_dummy_booking_data(90)
        df = pd.DataFrame(dummy_data)
    else:
        df = pd.DataFrame([d.dict() for d in request.data])
    
    # Validate minimum data points
    if len(df) < 14:
        return {
            "error": "Insufficient data",
            "message": f"Need at least 14 data points, got {len(df)}. Using dummy data for demonstration.",
            "rmse": 0,
            "predictions": []
        }

    df = create_features(df)

    scaled, scaler = preprocess(df)
    X, y = create_sequences(scaled)

    split = int(len(X) * 0.8)
    X_train, X_test = X[:split], X[split:]
    y_train, y_test = y[:split], y[split:]

    model = build_model((X.shape[1], X.shape[2]))
    model.fit(X_train, y_train, epochs=20, verbose=0)

    # Evaluate
    preds = model.predict(X_test, verbose=0)
    rmse = np.sqrt(mean_squared_error(y_test, preds))

    # Forecast
    future = forecast(model, scaled, scaler, df, request.forecast_days)

    # Add confidence interval
    final = []
    for item in future:
        lower, upper = confidence_interval(item["predicted"], rmse)

        final.append({
            "date": item["date"],
            "predicted": item["predicted"],
            "lower_bound": float(lower),
            "upper_bound": float(upper),
            "confidence": float(max(0, 1 - rmse))
        })

    return {
        "rmse": float(rmse),
        "predictions": final,
        "data_source": "dummy" if request.use_dummy_data else "real",
        "training_samples": len(X_train),
        "test_samples": len(X_test)
    }


# Predict 3 weeks ahead endpoint
@app.post("/predict-3weeks")
def predict_three_weeks(request: RequestData):
    """
    Specialized endpoint for 3-week (21-day) predictions
    Automatically uses dummy data if real data is insufficient
    """
    # Override forecast days to 21 (3 weeks)
    request.forecast_days = 21
    
    # Check if we have enough real data
    if not request.use_dummy_data and len(request.data) < 30:
        request.use_dummy_data = True
    
    # Call the main predict function
    result = predict(request)
    
    # Add weekly grouping for better visualization
    if "predictions" in result and result["predictions"]:
        weekly_summary = []
        predictions = result["predictions"]
        
        for week_num in range(3):
            start_idx = week_num * 7
            end_idx = min(start_idx + 7, len(predictions))
            week_data = predictions[start_idx:end_idx]
            
            if week_data:
                avg_predicted = sum(p["predicted"] for p in week_data) / len(week_data)
                avg_lower = sum(p["lower_bound"] for p in week_data) / len(week_data)
                avg_upper = sum(p["upper_bound"] for p in week_data) / len(week_data)
                
                weekly_summary.append({
                    "week": week_num + 1,
                    "start_date": week_data[0]["date"],
                    "end_date": week_data[-1]["date"],
                    "avg_predicted": round(avg_predicted, 2),
                    "avg_lower_bound": round(avg_lower, 2),
                    "avg_upper_bound": round(avg_upper, 2),
                    "total_predicted": round(sum(p["predicted"] for p in week_data), 2)
                })
        
        result["weekly_summary"] = weekly_summary
        result["forecast_period"] = "3 weeks (21 days)"
    
    return result


# Demo endpoint
@app.get("/demo")
def demo_prediction():
    """
    Demo endpoint that shows LSTM predictions with dummy data
    Returns 3-week forecast based on simulated booking patterns
    """
    dummy_data = generate_dummy_booking_data(90)
    
    request = RequestData(
        data=[DataPoint(**d) for d in dummy_data],
        forecast_days=21,
        use_dummy_data=True
    )
    
    result = predict_three_weeks(request)
    
    return {
        "title": "LSTM Model Predictions (Based on Dummy Booking Counts) for the Following 3 Weeks",
        "description": "This demonstration uses 90 days of simulated booking data to predict the next 21 days",
        "model": "LSTM Neural Network",
        "training_data_points": 90,
        "forecast_days": 21,
        **result
    }