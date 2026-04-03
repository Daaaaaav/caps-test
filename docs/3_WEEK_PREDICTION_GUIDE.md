# 3-Week LSTM Prediction Guide

## Overview

The LSTM service now supports **3-week (21-day) predictions** with automatic dummy data generation for demonstrations. This is perfect for showcasing the AI capabilities even without real booking data.

## Features

### 1. Automatic Dummy Data Generation
- Generates 90 days of realistic booking patterns
- Includes weekly seasonality (higher on weekdays)
- Adds random variation and monthly trends
- Perfect for demonstrations and testing

### 2. Weekly Summary
- Groups predictions by week
- Shows average daily bookings per week
- Calculates weekly totals
- Provides confidence ranges

### 3. Flexible Data Source
- Uses real data when available
- Automatically switches to dummy data if insufficient
- Can force dummy data for demonstrations

## API Endpoints

### 1. Standard Prediction (Any Duration)
```
POST http://127.0.0.1:8001/predict
```

**Request:**
```json
{
  "data": [
    {"date": "2026-01-01", "count": 5},
    {"date": "2026-01-02", "count": 7},
    ...
  ],
  "forecast_days": 21,
  "use_dummy_data": false
}
```

### 2. 3-Week Prediction (Specialized)
```
POST http://127.0.0.1:8001/predict-3weeks
```

**Request:**
```json
{
  "data": [],
  "use_dummy_data": true
}
```

**Response:**
```json
{
  "rmse": 1.23,
  "predictions": [
    {
      "date": "2026-04-01",
      "predicted": 8.5,
      "lower_bound": 6.2,
      "upper_bound": 10.8,
      "confidence": 0.85
    },
    ...
  ],
  "weekly_summary": [
    {
      "week": 1,
      "start_date": "2026-04-01",
      "end_date": "2026-04-07",
      "avg_predicted": 8.3,
      "avg_lower_bound": 6.1,
      "avg_upper_bound": 10.5,
      "total_predicted": 58.1
    },
    ...
  ],
  "data_source": "dummy",
  "forecast_period": "3 weeks (21 days)"
}
```

### 3. Demo Endpoint (Always Dummy Data)
```
GET http://127.0.0.1:8001/demo
```

**Response:**
```json
{
  "title": "LSTM Model Predictions (Based on Dummy Booking Counts) for the Following 3 Weeks",
  "description": "This demonstration uses 90 days of simulated booking data to predict the next 21 days",
  "model": "LSTM Neural Network",
  "training_data_points": 90,
  "forecast_days": 21,
  "rmse": 1.23,
  "predictions": [...],
  "weekly_summary": [...]
}
```

## Usage in Laravel

### Example 1: Get 3-Week Prediction with Dummy Data

```php
use App\Services\AI\LSTMClient;

$lstmClient = new LSTMClient();

// Get demo prediction (always uses dummy data)
$result = $lstmClient->getDemo();

if ($result) {
    echo $result['title'] . "\n\n";
    
    // Display weekly summary
    foreach ($result['weekly_summary'] as $week) {
        echo "Week {$week['week']}: ";
        echo "{$week['start_date']} to {$week['end_date']}\n";
        echo "  Average daily: {$week['avg_predicted']} bookings\n";
        echo "  Weekly total: {$week['total_predicted']} bookings\n\n";
    }
}
```

### Example 2: Get 3-Week Prediction with Real Data

```php
use App\Services\AI\LSTMClient;
use App\Services\AI\DataPreprocessor;

$preprocessor = new DataPreprocessor();
$lstmClient = new LSTMClient();
$companyId = Auth::user()->company_id;

// Get historical data
$timeSeries = $preprocessor->createTimeSeriesDataset('room_booking', $companyId, 90);

// Get 3-week prediction
$result = $lstmClient->predict3Weeks($timeSeries, false);

if ($result) {
    // Display predictions
    foreach ($result['predictions'] as $day) {
        echo "{$day['date']}: {$day['predicted']} bookings\n";
    }
}
```

### Example 3: Force Dummy Data for Demonstration

```php
$lstmClient = new LSTMClient();

// Force use of dummy data even if real data exists
$result = $lstmClient->predict3Weeks([], true);

// This is perfect for:
// - Demonstrations
// - Testing
// - Training
// - Presentations
```

## Testing

### Test Command

```powershell
php artisan ai:test-3weeks
```

**Output:**
```
===========================================
LSTM 3-Week Prediction Test
===========================================

Checking LSTM Service...
✓ LSTM service is running

Fetching 3-week prediction with dummy data...
===========================================
LSTM Model Predictions (Based on Dummy Booking Counts) for the Following 3 Weeks
===========================================

This demonstration uses 90 days of simulated booking data to predict the next 21 days

Model Information:
  Model: LSTM Neural Network
  Training Data: 90 days
  Forecast Period: 21 days
  RMSE: 1.2345
  Data Source: dummy

Weekly Summary:
┌────────┬─────────────────────────────┬───────────┬──────────────┬──────────────┐
│ Week   │ Period                      │ Avg Daily │ Weekly Total │ Range        │
├────────┼─────────────────────────────┼───────────┼──────────────┼──────────────┤
│ Week 1 │ 2026-04-01 to 2026-04-07   │ 8.3       │ 58           │ 6.1 - 10.5   │
│ Week 2 │ 2026-04-08 to 2026-04-14   │ 8.5       │ 60           │ 6.3 - 10.7   │
│ Week 3 │ 2026-04-15 to 2026-04-21   │ 8.7       │ 61           │ 6.5 - 10.9   │
└────────┴─────────────────────────────┴───────────┴──────────────┴──────────────┘

Daily Predictions (First 7 days):
┌────────────┬───────────┬───────────┬─────────────┬─────────────┬────────────┐
│ Date       │ Day       │ Predicted │ Lower Bound │ Upper Bound │ Confidence │
├────────────┼───────────┼───────────┼─────────────┼─────────────┼────────────┤
│ 2026-04-01 │ Tuesday   │ 8.5       │ 6.2         │ 10.8        │ 85.0%      │
│ 2026-04-02 │ Wednesday │ 8.3       │ 6.0         │ 10.6        │ 85.0%      │
│ 2026-04-03 │ Thursday  │ 8.7       │ 6.4         │ 11.0        │ 85.0%      │
│ 2026-04-04 │ Friday    │ 8.2       │ 5.9         │ 10.5        │ 85.0%      │
│ 2026-04-05 │ Saturday  │ 7.1       │ 4.8         │ 9.4         │ 85.0%      │
│ 2026-04-06 │ Sunday    │ 7.3       │ 5.0         │ 9.6         │ 85.0%      │
│ 2026-04-07 │ Monday    │ 8.4       │ 6.1         │ 10.7        │ 85.0%      │
└────────────┴───────────┴───────────┴─────────────┴─────────────┴────────────┘

Summary Statistics:
  Total Predicted (21 days): 179 bookings
  Average Daily: 8.5 bookings
  Average Confidence: 85.0%

===========================================
Test completed successfully!
===========================================
```

## Dummy Data Characteristics

The generated dummy data includes:

1. **Base Pattern**: 8 bookings per day average
2. **Weekly Seasonality**: 
   - Weekdays (Mon-Fri): 10-14 bookings
   - Weekends (Sat-Sun): 6-10 bookings
3. **Random Variation**: ±2-3 bookings per day
4. **Monthly Trend**: Slight increase over time (0.02 per day)
5. **Realistic Range**: 1-16 bookings per day

## Visualization Example

You can visualize the predictions in your frontend:

```javascript
// Fetch predictions
fetch('http://127.0.0.1:8001/demo')
  .then(response => response.json())
  .then(data => {
    const labels = data.predictions.map(p => p.date);
    const predicted = data.predictions.map(p => p.predicted);
    const lowerBound = data.predictions.map(p => p.lower_bound);
    const upperBound = data.predictions.map(p => p.upper_bound);
    
    // Use Chart.js or similar to display
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Predicted',
            data: predicted,
            borderColor: '#3b82f6',
            fill: false
          },
          {
            label: 'Lower Bound',
            data: lowerBound,
            borderColor: '#94a3b8',
            borderDash: [5, 5],
            fill: false
          },
          {
            label: 'Upper Bound',
            data: upperBound,
            borderColor: '#94a3b8',
            borderDash: [5, 5],
            fill: false
          }
        ]
      }
    });
  });
```

## Use Cases

### 1. Demonstrations
Show stakeholders how the AI system works without needing real data.

### 2. Testing
Test the prediction pipeline end-to-end with consistent data.

### 3. Training
Train staff on how to interpret predictions.

### 4. Development
Develop frontend visualizations before real data is available.

### 5. Presentations
Create impressive presentations with realistic-looking predictions.

## Customizing Dummy Data

To modify the dummy data generation, edit `LSTM_Service.py`:

```python
def generate_dummy_booking_data(days=90):
    # Adjust base count
    base_count = 10  # Change from 8 to 10
    
    # Adjust weekday pattern
    if day_of_week < 5:
        count = base_count + random.randint(3, 8)  # Higher range
    else:
        count = base_count + random.randint(-3, 1)  # Lower range
    
    # Adjust trend
    trend = i * 0.05  # Stronger trend
    
    return data
```

## Troubleshooting

### Issue: Predictions look unrealistic

**Solution**: Adjust the dummy data generation parameters in `LSTM_Service.py`

### Issue: RMSE is too high

**Solution**: This is normal for dummy data. Real data will have better RMSE.

### Issue: Weekly summary not showing

**Solution**: Ensure you're using the `/predict-3weeks` endpoint or `/demo` endpoint

## Next Steps

1. **Start LSTM Service**: `start_lstm_service.bat`
2. **Test 3-Week Predictions**: `php artisan ai:test-3weeks`
3. **View Demo**: Open `http://127.0.0.1:8001/demo` in browser
4. **Integrate in UI**: Use the predictions in your dashboard

## Summary

3-week predictions ready
Automatic dummy data generation
Weekly summary included
Confidence intervals provided
Easy to test and demonstrate
Works with or without real data

Perfect for demonstrations with the title: **"LSTM Model Predictions (Based on Dummy Booking Counts) for the Following 3 Weeks"**
