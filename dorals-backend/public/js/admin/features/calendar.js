import { apiFetch } from "../core/api.js";
import { dom } from "../core/dom.js";
import { state } from "../core/state.js";

export async function loadCalendarHeatmap(year = state.calendarYear, month = state.calendarMonth) {
  try {
    state.calendarYear = year;
    state.calendarMonth = month;

    const data = await apiFetch("/analytics/calendar-density", {
      params: { year, month }
    });

    renderCalendarHeatmap(data);
  } catch (e) {
    console.error("Error loading calendar density:", e);
  }
}

export function renderCalendarHeatmap(data) {
  const container = dom.appointmentCalendar();
  if (!container) return;

  container.innerHTML = "";

  const year = data.year;
  const month = data.month;
  const counts = data.counts_by_day || {};

  const monthNames = [
    "January","February","March","April","May","June",
    "July","August","September","October","November","December"
  ];

  const labelEl = dom.calendarMonthLabel();
  if (labelEl) labelEl.textContent = `${monthNames[month - 1]} ${year}`;

  const firstDate = new Date(year, month - 1, 1);
  const firstWeekday = firstDate.getDay();
  const daysInMonth = new Date(year, month, 0).getDate();
  const todayStr = new Date().toISOString().split("T")[0];

  for (let i = 0; i < firstWeekday; i++) {
    const cell = document.createElement("div");
    cell.className = "h-10";
    container.appendChild(cell);
  }

  for (let day = 1; day <= daysInMonth; day++) {
    const dateStr = `${year}-${String(month).padStart(2,"0")}-${String(day).padStart(2,"0")}`;
    const count = counts[dateStr] || 0;

    let bgClass = "bg-gray-50 text-gray-600";
    if (count > 0 && count <= 3) bgClass = "bg-green-100 text-green-800";
    else if (count >= 4 && count <= 7) bgClass = "bg-amber-100 text-amber-800";
    else if (count >= 8) bgClass = "bg-red-200 text-red-900";

    const isToday = dateStr === todayStr;

    const cell = document.createElement("div");
    cell.className = [
      "h-10 flex flex-col items-center justify-center rounded-xl border text-[11px] leading-tight",
      "border-gray-100",
      bgClass,
      isToday ? "ring-2 ring-blue-400 ring-offset-2 ring-offset-white" : "",
      "hover:shadow-sm transition-shadow"
    ].join(" ");

    cell.innerHTML = `
      <div class="font-semibold">${day}</div>
      ${count > 0 ? `<div class="mt-0.5 text-[10px] opacity-80">${count}</div>` : ""}
    `;

    container.appendChild(cell);
  }
}

export function bindCalendarNav() {
  document.addEventListener("click", (e) => {
    if (e.target.closest("[data-cal='prev']")) {
      state.calendarMonth--;
      if (state.calendarMonth <= 0) { state.calendarMonth = 12; state.calendarYear--; }
      loadCalendarHeatmap(state.calendarYear, state.calendarMonth);
    }

    if (e.target.closest("[data-cal='next']")) {
      state.calendarMonth++;
      if (state.calendarMonth >= 13) { state.calendarMonth = 1; state.calendarYear++; }
      loadCalendarHeatmap(state.calendarYear, state.calendarMonth);
    }
  });
}
