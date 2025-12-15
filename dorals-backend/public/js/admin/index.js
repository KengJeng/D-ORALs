import { state } from "./core/state.js";
import { dom } from "./core/dom.js";
import { startClock } from "./ui/clock.js";

import { loadStats } from "./features/dashboard.js";
import { loadQueue, loadNextPatient, updateStatus } from "./features/queue.js";
import { loadCalendarHeatmap, bindCalendarNav } from "./features/calendar.js";

import { bindNewAppointmentForm, bindModalBackdropClose } from "./features/appointments.js";
import { bindNav } from "./features/sections.js";
import { registerGlobals } from "./globals.js";

import { goToPatientsPage, openEditPatientModal, confirmDeletePatient } from "./features/patients.js";
import { editService, deleteService } from "./features/services.js";
import { loadAuditLogs } from "./features/audit.js";
import { exportAppointments } from "./features/reports.js";

// Auth redirect
if (!state.authToken) window.location.href = "/admin/login";

// Sidebar name
if (state.admin) {
  dom.sidebarAdminName() && (dom.sidebarAdminName().textContent = state.admin.name);
}

// date min
const today = new Date().toISOString().split("T")[0];
const dateInput = document.getElementById("appointmentDate");
if (dateInput) dateInput.setAttribute("min", today);

async function loadDashboard() {
  await Promise.all([loadStats(), loadQueue(), loadNextPatient(), loadCalendarHeatmap()]);
}

document.addEventListener("DOMContentLoaded", () => {
  registerGlobals();            // keeps old onclick alive while you migrate
  bindNav();                    // optional if you add data-section
  bindCalendarNav();
  bindNewAppointmentForm();
  bindModalBackdropClose();
  registerGlobals();

  loadDashboard();
  setInterval(loadDashboard, 40000);
  startClock();
});

// Unified action handler (recommended)
document.addEventListener("click", (e) => {
  const btn = e.target.closest("[data-action]");
  if (!btn) return;

  const action = btn.dataset.action;
  const id = btn.dataset.id;

  if (action === "complete") {
    updateStatus(id, "Completed").then(loadDashboard);
  }

  if (action === "patients-prev") goToPatientsPage("prev");
  if (action === "patients-next") goToPatientsPage("next");

  if (action === "patients-page") goToPatientsPage(btn.dataset.page);

  if (action === "patient-edit") openEditPatientModal(id);
  if (action === "patient-delete") confirmDeletePatient(id);

  if (action === "service-edit") editService(id);
  if (action === "service-delete") deleteService(id);

  if (action === "audit-prev") loadAuditLogs(Math.max(1, (window.__auditPage || 1) - 1));
  if (action === "audit-next") loadAuditLogs((window.__auditPage || 1) + 1);

  if (action === "export-appts") exportAppointments(btn);
});
