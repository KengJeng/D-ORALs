import { showSection } from "./features/sections.js";
import {
  searchPatients,
  goToPatientsPage,
  openEditPatientModal,
  submitEditPatient,
  confirmDeletePatient
} from "./features/patients.js";
import { displayServices, editService, deleteService } from "./features/services.js";
import { loadAuditLogs, filterAuditLogs } from "./features/audit.js";
import { showNewAppointmentModal } from "./features/appointments.js";
import { exportAppointments } from "./features/reports.js";
import { callNextPatient } from "./features/queue.js";

export function registerGlobals() {
  window.showSection = (section, ev) => showSection(section, ev?.target || null);

  // Patients
  window.searchPatients = searchPatients;
  window.goToPatientsPage = goToPatientsPage;
  window.openEditPatientModal = openEditPatientModal;
  window.submitEditPatient = submitEditPatient;
  window.confirmDeletePatient = confirmDeletePatient;
  window.callNextPatient = callNextPatient;

  // Services
  window.displayServices = displayServices;
  window.editService = editService;
  window.deleteService = deleteService;

  // Queue
  window.callNextPatient = callNextPatient;

  // Audit
  window.loadAuditLogs = loadAuditLogs;
  window.filterAuditLogs = () => {
    const val = document.getElementById("auditFilter")?.value || "all";
    filterAuditLogs(val);
  };

  // Appointments + Export
  window.showNewAppointmentModal = showNewAppointmentModal;
  window.exportAppointments = exportAppointments;
}
