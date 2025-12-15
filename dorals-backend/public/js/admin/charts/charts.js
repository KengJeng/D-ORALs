import { state } from "../core/state.js";

export function setChart(key, instance) {
  if (state.charts[key]) {
    try { state.charts[key].destroy(); } catch (_) {}
  }
  state.charts[key] = instance;
}

export function destroyChart(key) {
  if (!state.charts[key]) return;
  try { state.charts[key].destroy(); } catch (_) {}
  delete state.charts[key];
}
