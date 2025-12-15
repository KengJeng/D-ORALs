import { apiFetch } from "../core/api.js";
import { dom } from "../core/dom.js";

export async function loadStats() {
  try {
    const stats = await apiFetch("/dashboard/stats");
    dom.todayQueue() && (dom.todayQueue().textContent = stats.today_queue || 0);
    dom.completedToday() && (dom.completedToday().textContent = stats.completed_today || 0);
    dom.totalPatients() && (dom.totalPatients().textContent = stats.total_patients || 0);
    dom.pendingToday() && (dom.pendingToday().textContent = stats.pending_today || 0);
  } catch (e) {
    console.error("Error loading stats:", e);
  }
}
