import { apiFetch } from "../core/api.js";
import { setChart } from "../charts/charts.js";
import { getExportRange } from "../utils/date.js";

export async function loadReportsAnalytics() {
  await Promise.all([
    loadAppointmentAnalytics(),
    loadPatientDemographics(),
  ]);

  renderPredictiveCharts();
}

export async function loadAppointmentAnalytics() {
  try {
    const data = await apiFetch("/analytics/appointments");

    document.getElementById("reportTotalApts") && (document.getElementById("reportTotalApts").textContent = data.total_appointments || 0);
    document.getElementById("reportCompletionRate") && (document.getElementById("reportCompletionRate").textContent = (data.completion_rate || 0) + "%");
    document.getElementById("reportCancelRate") && (document.getElementById("reportCancelRate").textContent = (data.cancellation_rate || 0) + "%");
    document.getElementById("reportAvgPerDay") && (document.getElementById("reportAvgPerDay").textContent = data.avg_per_day || 0);

    // If you already have your exact chart configs, paste them here.
    // IMPORTANT: Chart must be globally available (script include), since this is public/ JS.

    const statusCanvas = document.getElementById("appointmentStatusChart");
    if (statusCanvas?.getContext && window.Chart) {
      const ctx = statusCanvas.getContext("2d");
      setChart("appointmentStatus", new Chart(ctx, {
        type: "pie",
        data: {
          labels: (data.status_breakdown?.labels || []),
          datasets: [{ data: (data.status_breakdown?.values || []) }],
        },
        options: { responsive: true, plugins: { legend: { position: "bottom" } } }
      }));
    }

    const peakCanvas = document.getElementById("peakDaysChart");
    if (peakCanvas?.getContext && window.Chart) {
      const ctx = peakCanvas.getContext("2d");
      setChart("peakDays", new Chart(ctx, {
        type: "bar",
        data: {
          labels: (data.peak_days?.labels || []),
          datasets: [{ label: "Appointments", data: (data.peak_days?.values || []) }],
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
      }));
    }

    const monthlyCanvas = document.getElementById("monthlyComparisonChart");
    if (monthlyCanvas?.getContext && window.Chart) {
      const ctx = monthlyCanvas.getContext("2d");
      setChart("monthlyComparison", new Chart(ctx, {
        type: "line",
        data: {
          labels: (data.monthly?.labels || []),
          datasets: [{ label: "Appointments", data: (data.monthly?.values || []), borderWidth: 2, tension: 0.3 }]
        },
        options: { responsive: true, plugins: { legend: { position: "bottom" } } }
      }));
    }
  } catch (e) {
    console.error("Error loading appointment analytics:", e);
  }
}

export async function loadPatientDemographics() {
  try {
    const data = await apiFetch("/analytics/demographics");

    document.getElementById("demoTotalPatients") && (document.getElementById("demoTotalPatients").textContent = data.total_patients || 0);
    document.getElementById("demoNewThisMonth") && (document.getElementById("demoNewThisMonth").textContent = data.new_this_month || 0);
    document.getElementById("demoActivePatients") && (document.getElementById("demoActivePatients").textContent = data.active_patients || 0);
    document.getElementById("demoAvgVisits") && (document.getElementById("demoAvgVisits").textContent = data.avg_visits || 0);

    const genderData = data.gender || { labels: [], values: [] };
    const barangayData = data.barangays || { labels: [], values: [] };

    const genderCanvas = document.getElementById("genderChart");
    if (genderCanvas?.getContext && window.Chart) {
      const ctx = genderCanvas.getContext("2d");
      setChart("gender", new Chart(ctx, {
        type: "pie",
        data: { labels: genderData.labels, datasets: [{ data: genderData.values }] },
        options: { responsive: true, plugins: { legend: { position: "bottom" } } }
      }));
    }

    const barangayCanvas = document.getElementById("barangayChart");
    if (barangayCanvas?.getContext && window.Chart) {
      const ctx = barangayCanvas.getContext("2d");
      setChart("barangay", new Chart(ctx, {
        type: "bar",
        data: { labels: barangayData.labels, datasets: [{ label: "Patients", data: barangayData.values, borderWidth: 1 }] },
        options: { responsive: true, indexAxis: "y", plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
      }));
    }
  } catch (e) {
    console.error("Error loading patient demographics:", e);
  }
}

export function renderPredictiveCharts() {
  // You already populate window.reportChartsData from backend in Blade (as per your code).
  const chartsData = window.reportChartsData || {};
  if (!window.Chart) return;

  const combinedLabels = chartsData.combinedLabels || [];
  const combinedHistorical = chartsData.combinedHistorical || [];
  const combinedForecast = chartsData.combinedForecast || [];
  const serviceNames = chartsData.serviceNames || [];
  const serviceCounts = chartsData.serviceCounts || [];

  const combinedCanvas = document.getElementById("combinedForecastChart");
  if (combinedCanvas?.getContext) {
    const ctx = combinedCanvas.getContext("2d");
    setChart("combinedForecast", new Chart(ctx, {
      type: "line",
      data: {
        labels: combinedLabels,
        datasets: [
          { label: "Historical", data: combinedHistorical, borderWidth: 2, tension: 0.3, fill: false },
          { label: "Forecast", data: combinedForecast, borderWidth: 2, tension: 0.3, borderDash: [6, 4], fill: false },
        ]
      },
      options: { responsive: true, maintainAspectRatio: true }
    }));
  }

  const serviceCanvas = document.getElementById("serviceForecastChart");
  if (serviceCanvas?.getContext) {
    const ctx = serviceCanvas.getContext("2d");
    setChart("serviceForecast", new Chart(ctx, {
      type: "bar",
      data: {
        labels: serviceNames,
        datasets: [{ label: "Forecasted Service Count", data: serviceCounts, borderWidth: 1 }]
      },
      options: { responsive: true, maintainAspectRatio: true }
    }));
  }
}

export function exportAppointments(buttonElement) {
  const baseUrl = buttonElement.dataset.exportUrl;
  const rangeSelect = document.getElementById("exportRange");
  if (!baseUrl || !rangeSelect) return;

  const rangeKey = rangeSelect.value;
  const { from, to } = getExportRange(rangeKey);

  const params = new URLSearchParams();
  if (from) params.append("from", from);
  if (to) params.append("to", to);

  const finalUrl = baseUrl + (params.toString() ? "?" + params.toString() : "");
  window.location.href = finalUrl;
}
