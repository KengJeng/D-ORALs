import { state } from "../core/state.js";
import { dom } from "../core/dom.js";
import { displayPatients } from "./patients.js";
import { displayServices } from "./services.js";
import { loadReportsAnalytics } from "./reports.js";
import { loadAuditLogs } from "./audit.js";

const sections = ["queue", "patients", "services", "reports", "settings", "audit"];
const titles = {
  queue: "Appointments & Queue",
  patients: "View Patients",
  services: "Manage Services",
  reports: "Reports & Analytics",
  settings: "Settings",
  audit: "Audit Trail",
};

export function showSection(section, triggerEl = null) {
  sections.forEach(s => {
    const el = document.getElementById(s + "Section");
    el && el.classList.add("hidden");
  });

  const target = document.getElementById(section + "Section");
  target && target.classList.remove("hidden");

  dom.pageTitle() && (dom.pageTitle().textContent = titles[section] || "Dashboard");

  document.querySelectorAll(".nav-item").forEach(item => item.classList.remove("active"));
  if (triggerEl) triggerEl.closest(".nav-item")?.classList.add("active");

  if (section === "patients") displayPatients(1);
  if (section === "services") displayServices();
  if (section === "reports") loadReportsAnalytics();
  if (section === "audit") loadAuditLogs(1);
}

export function bindNav() {
  document.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-section]");
    if (!btn) return;
    showSection(btn.dataset.section, btn);
  });
}
