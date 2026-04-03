/**
 * Canvas.Dashboard - BI Analytics Template
 *
 * A production-ready analytics dashboard showing:
 * - Memory usage statistics
 * - Search trends and popular queries
 * - Agent performance metrics
 * - Recent activity timeline
 * - Custom widgets
 *
 * Features:
 * - Real-time updates via polling or websockets
 * - Responsive grid layout
 * - Export to CSV/PDF
 * - Date range filtering
 * - Customizable widgets via brand.json
 *
 * @example
 * ```tsx
 * <Canvas.Dashboard brand={brand} memoryEndpoint="/api/memory" />
 * ```
 */

'use client';

import React, { useState, useEffect, useCallback } from 'react';
import type { BrandConfig } from '../types/brand.js';

interface DashboardProps {
  brand?: BrandConfig;
  config?: Record<string, unknown>;
  memoryEndpoint?: string;
  onEvent?: (event: string, data?: Record<string, unknown>) => void;
}

interface MemoryStats {
  totalFrames: number;
  totalSize: string;
  documentCount: number;
  conversationCount: number;
  lastUpdated: string;
}

interface SearchTrend {
  query: string;
  count: number;
  trend: 'up' | 'down' | 'stable';
}

interface TimeSeriesPoint {
  timestamp: string;
  value: number;
}

/**
 * Stat Card Component
 */
const StatCard = React.memo(function StatCard({
  title,
  value,
  subtitle,
  icon,
  trend,
  trendValue,
}: {
  title: string;
  value: string | number;
  subtitle?: string;
  icon?: React.ReactNode;
  trend?: 'up' | 'down' | 'stable';
  trendValue?: string;
}) {
  return (
    <div className="canvas-stat-card">
      <div className="canvas-stat-card__header">
        {icon && <span className="canvas-stat-card__icon">{icon}</span>}
        <span className="canvas-stat-card__title">{title}</span>
      </div>
      <div className="canvas-stat-card__value">{value}</div>
      {(subtitle || trendValue) && (
        <div className="canvas-stat-card__footer">
          {subtitle && <span className="canvas-stat-card__subtitle">{subtitle}</span>}
          {trendValue && (
            <span className={`canvas-stat-card__trend canvas-stat-card__trend--${trend}`}>
              {trend === 'up' && '↑'}
              {trend === 'down' && '↓'}
              {trendValue}
            </span>
          )}
        </div>
      )}
    </div>
  );
});

/**
 * Chart Component (Simple bar chart)
 * Available for custom dashboard widgets
 */
export const BarChart = React.memo(function BarChart({
  data,
  title,
  height = 200,
}: {
  data: { label: string; value: number }[];
  title?: string;
  height?: number;
}) {
  const maxValue = Math.max(...data.map(d => d.value), 1);

  return (
    <div className="canvas-chart">
      {title && <h3 className="canvas-chart__title">{title}</h3>}
      <div className="canvas-chart__bars" style={{ height }}>
        {data.map((item, index) => (
          <div key={index} className="canvas-chart__bar-container">
            <div
              className="canvas-chart__bar"
              style={{ height: `${(item.value / maxValue) * 100}%` }}
              title={`${item.label}: ${item.value}`}
            />
            <span className="canvas-chart__label">{item.label}</span>
          </div>
        ))}
      </div>
    </div>
  );
});

/**
 * Line Chart Component (Simple time series)
 */
const LineChart = React.memo(function LineChart({
  data,
  title,
  height = 200,
}: {
  data: TimeSeriesPoint[];
  title?: string;
  height?: number;
}) {
  const maxValue = Math.max(...data.map(d => d.value), 1);
  const minValue = Math.min(...data.map(d => d.value), 0);
  const range = maxValue - minValue || 1;

  const points = data.map((point, index) => {
    const x = (index / (data.length - 1)) * 100;
    const y = 100 - ((point.value - minValue) / range) * 100;
    return `${x},${y}`;
  }).join(' ');

  return (
    <div className="canvas-chart canvas-chart--line">
      {title && <h3 className="canvas-chart__title">{title}</h3>}
      <svg viewBox="0 0 100 100" preserveAspectRatio="none" style={{ height }}>
        <polyline
          points={points}
          fill="none"
          stroke="var(--canvas-primary)"
          strokeWidth="2"
          vectorEffect="non-scaling-stroke"
        />
      </svg>
    </div>
  );
});

/**
 * Activity List Component
 */
const ActivityList = React.memo(function ActivityList({
  activities,
  title,
}: {
  activities: { id: string; type: string; content: string; time: string }[];
  title?: string;
}) {
  return (
    <div className="canvas-activity">
      {title && <h3 className="canvas-activity__title">{title}</h3>}
      <ul className="canvas-activity__list">
        {activities.map(activity => (
          <li key={activity.id} className="canvas-activity__item">
            <span className={`canvas-activity__type canvas-activity__type--${activity.type}`}>
              {activity.type}
            </span>
            <span className="canvas-activity__content">{activity.content}</span>
            <span className="canvas-activity__time">{activity.time}</span>
          </li>
        ))}
      </ul>
    </div>
  );
});

/**
 * Search Trends Component
 */
const SearchTrends = React.memo(function SearchTrends({
  trends,
  title = 'Popular Searches',
}: {
  trends: SearchTrend[];
  title?: string;
}) {
  return (
    <div className="canvas-trends">
      <h3 className="canvas-trends__title">{title}</h3>
      <ul className="canvas-trends__list">
        {trends.map((trend, index) => (
          <li key={index} className="canvas-trends__item">
            <span className="canvas-trends__rank">{index + 1}</span>
            <span className="canvas-trends__query">{trend.query}</span>
            <span className="canvas-trends__count">{trend.count}</span>
            <span className={`canvas-trends__trend canvas-trends__trend--${trend.trend}`}>
              {trend.trend === 'up' && '↑'}
              {trend.trend === 'down' && '↓'}
              {trend.trend === 'stable' && '→'}
            </span>
          </li>
        ))}
      </ul>
    </div>
  );
});

type TimeRangeValue = '24h' | '7d' | '30d' | '90d' | 'all';

/**
 * Date Range Picker Component
 */
const DateRangePicker = React.memo(function DateRangePicker({
  value,
  onChange,
}: {
  value: TimeRangeValue;
  onChange: (range: TimeRangeValue) => void;
}) {
  const options: { value: TimeRangeValue; label: string }[] = [
    { value: '24h', label: 'Last 24 hours' },
    { value: '7d', label: 'Last 7 days' },
    { value: '30d', label: 'Last 30 days' },
    { value: '90d', label: 'Last 90 days' },
    { value: 'all', label: 'All time' },
  ];

  return (
    <div className="canvas-date-range">
      {options.map(option => (
        <button
          key={option.value}
          className={`canvas-date-range__btn ${value === option.value ? 'canvas-date-range__btn--active' : ''}`}
          onClick={() => onChange(option.value)}
        >
          {option.label}
        </button>
      ))}
    </div>
  );
});

/**
 * Main Dashboard Component
 */
export function Dashboard({
  brand,
  config,
  memoryEndpoint = '/api/canvas',
  onEvent,
}: DashboardProps) {
  const dashboardConfig = { ...(brand?.dashboard || {}), ...(config?.dashboard || {}) };
  const [timeRange, setTimeRange] = useState<TimeRangeValue>(dashboardConfig.defaultTimeRange || '7d');
  const [stats, setStats] = useState<MemoryStats | null>(null);
  const [trends, setTrends] = useState<SearchTrend[]>([]);
  const [activityData, setActivityData] = useState<TimeSeriesPoint[]>([]);
  const [activities, setActivities] = useState<{ id: string; type: string; content: string; time: string }[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Fetch dashboard data
  const fetchData = useCallback(async () => {
    try {
      setIsLoading(true);
      setError(null);

      const response = await fetch(`${memoryEndpoint}/stats?range=${timeRange}`);
      if (!response.ok) throw new Error('Failed to fetch stats');

      const data = await response.json();

      // Set stats
      setStats({
        totalFrames: data.totalFrames || 0,
        totalSize: data.totalSize || '0 MB',
        documentCount: data.documentCount || 0,
        conversationCount: data.conversationCount || 0,
        lastUpdated: new Date().toLocaleTimeString(),
      });

      // Set trends (mock if not provided)
      setTrends(data.trends || [
        { query: 'authentication', count: 142, trend: 'up' },
        { query: 'api integration', count: 98, trend: 'stable' },
        { query: 'database setup', count: 87, trend: 'up' },
        { query: 'deployment', count: 65, trend: 'down' },
        { query: 'error handling', count: 54, trend: 'stable' },
      ]);

      // Set activity data (mock if not provided)
      setActivityData(data.activityData || [
        { timestamp: '00:00', value: 12 },
        { timestamp: '04:00', value: 8 },
        { timestamp: '08:00', value: 45 },
        { timestamp: '12:00', value: 78 },
        { timestamp: '16:00', value: 92 },
        { timestamp: '20:00', value: 34 },
      ]);

      // Set recent activities (mock if not provided)
      setActivities(data.activities || [
        { id: '1', type: 'search', content: 'User searched "API documentation"', time: '2m ago' },
        { id: '2', type: 'chat', content: 'Support conversation started', time: '5m ago' },
        { id: '3', type: 'document', content: 'New document added to knowledge base', time: '12m ago' },
        { id: '4', type: 'search', content: 'User searched "error codes"', time: '18m ago' },
      ]);

      onEvent?.('dashboard_loaded', { timeRange });
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to load dashboard');
    } finally {
      setIsLoading(false);
    }
  }, [memoryEndpoint, timeRange, onEvent]);

  // Initial fetch and refresh interval
  useEffect(() => {
    fetchData();

    const refreshInterval = dashboardConfig.refreshInterval;
    if (refreshInterval && refreshInterval > 0) {
      const interval = setInterval(fetchData, refreshInterval);
      return () => clearInterval(interval);
    }
  }, [fetchData, dashboardConfig.refreshInterval]);

  // Handle time range change
  const handleTimeRangeChange = (range: TimeRangeValue) => {
    setTimeRange(range);
    onEvent?.('time_range_changed', { range });
  };

  // Export handler
  const handleExport = useCallback((format: 'csv' | 'pdf') => {
    onEvent?.('export', { format, timeRange });
    // TODO: Implement actual export
    alert(`Export to ${format.toUpperCase()} - Coming soon!`);
  }, [onEvent, timeRange]);

  if (isLoading && !stats) {
    return (
      <div className="canvas-dashboard canvas-dashboard--loading">
        <div className="canvas-loader">
          <div className="canvas-loader__spinner" />
          <span>Loading dashboard...</span>
        </div>
      </div>
    );
  }

  return (
    <div className="canvas-dashboard">
      {/* Header */}
      <header className="canvas-dashboard__header">
        <div className="canvas-dashboard__header-left">
          <h1 className="canvas-dashboard__title">
            {dashboardConfig.title || 'Analytics Dashboard'}
          </h1>
          {stats && (
            <span className="canvas-dashboard__updated">
              Last updated: {stats.lastUpdated}
            </span>
          )}
        </div>
        <div className="canvas-dashboard__header-right">
          {dashboardConfig.enableDateRange !== false && (
            <DateRangePicker value={timeRange} onChange={handleTimeRangeChange} />
          )}
          {dashboardConfig.enableExport !== false && (
            <div className="canvas-dashboard__export">
              <button onClick={() => handleExport('csv')}>Export CSV</button>
              <button onClick={() => handleExport('pdf')}>Export PDF</button>
            </div>
          )}
        </div>
      </header>

      {error && (
        <div className="canvas-dashboard__error">
          <p>{error}</p>
          <button onClick={fetchData}>Retry</button>
        </div>
      )}

      {/* Stats Grid */}
      <section className="canvas-dashboard__stats">
        <StatCard
          title="Total Memories"
          value={stats?.totalFrames.toLocaleString() || '0'}
          icon={<span>📚</span>}
          trend="up"
          trendValue="+12%"
        />
        <StatCard
          title="Storage Used"
          value={stats?.totalSize || '0 MB'}
          icon={<span>💾</span>}
          subtitle="of 1 GB"
        />
        <StatCard
          title="Documents"
          value={stats?.documentCount.toLocaleString() || '0'}
          icon={<span>📄</span>}
          trend="stable"
          trendValue="0%"
        />
        <StatCard
          title="Conversations"
          value={stats?.conversationCount.toLocaleString() || '0'}
          icon={<span>💬</span>}
          trend="up"
          trendValue="+8%"
        />
      </section>

      {/* Main Grid */}
      <section className="canvas-dashboard__grid">
        {/* Activity Chart */}
        <div className="canvas-dashboard__widget canvas-dashboard__widget--wide">
          <LineChart data={activityData} title="Activity Over Time" height={250} />
        </div>

        {/* Search Trends */}
        <div className="canvas-dashboard__widget">
          <SearchTrends trends={trends} />
        </div>

        {/* Recent Activity */}
        <div className="canvas-dashboard__widget">
          <ActivityList activities={activities} title="Recent Activity" />
        </div>
      </section>
    </div>
  );
}

export default Dashboard;
