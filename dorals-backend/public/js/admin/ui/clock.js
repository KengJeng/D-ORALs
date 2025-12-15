import { dom } from "../core/dom.js";

export function startClock() {
  const el = dom.currentTimeDisplay();
  if (!el) return;

  function updateTime() {
    const now = new Date();
    let hours = now.getHours();
    const ampm = hours >= 12 ? "PM" : "AM";
    hours = hours % 12 || 12;
    const minutes = String(now.getMinutes()).padStart(2, "0");
    const seconds = String(now.getSeconds()).padStart(2, "0");
    el.textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
  }

  updateTime();
  setInterval(updateTime, 1000);
}
