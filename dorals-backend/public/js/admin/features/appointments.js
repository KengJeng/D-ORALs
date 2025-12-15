import { apiFetch } from "../core/api.js";
import { showAlert } from "../ui/alerts.js";
import { openModal, closeModal } from "../ui/modals.js";

let allPatients = [];
let allServices = [];

export async function showNewAppointmentModal() {
  openModal("newAppointmentModal");
  await Promise.all([loadPatientsForAppointment(), loadServicesForAppointment()]);
}

export async function loadPatientsForAppointment() {
  try {
    const data = await apiFetch("/patients");
    allPatients = data.data || [];

    const sel = document.getElementById("appointmentPatient");
    if (!sel) return;

    sel.innerHTML = '<option value="">Select patient</option>' +
      allPatients.map(p => `<option value="${p.patient_id}">${p.first_name} ${p.last_name}</option>`).join("");
  } catch (e) {
    console.error("Error loading patients:", e);
  }
}

export async function loadServicesForAppointment() {
  try {
    allServices = await apiFetch("/services");

    const box = document.getElementById("appointmentServices");
    if (!box) return;

    box.innerHTML = allServices.map(s => `
      <label class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg cursor-pointer transition">
        <input type="checkbox" value="${s.service_id}" class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
        <span class="text-gray-700">${s.name} <span class="text-gray-400 text-sm">(${s.duration} min)</span></span>
      </label>
    `).join("");
  } catch (e) {
    console.error("Error loading services:", e);
  }
}

export function bindNewAppointmentForm() {
  const form = document.getElementById("newAppointmentForm");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const patientId = document.getElementById("appointmentPatient")?.value;
    const date = document.getElementById("appointmentDate")?.value;
    const checks = document.querySelectorAll("#appointmentServices input:checked");
    const serviceIds = Array.from(checks).map(c => parseInt(c.value));

    if (!patientId || !date || serviceIds.length === 0) {
      showAlert("Fill all fields", "error");
      return;
    }

    try {
      const data = await apiFetch("/appointments", {
        method: "POST",
        body: {
          patient_id: parseInt(patientId),
          scheduled_date: date,
          service_ids: serviceIds
        }
      });

      showAlert(`Created! Queue #${data.appointment?.queue_number}`, "success");
      closeModal("newAppointmentModal");
      form.reset();
    } catch (err) {
      showAlert(err.message || "Failed", "error");
    }
  });
}

export function bindModalBackdropClose() {
  document.addEventListener("click", (e) => {
    const modal = document.getElementById("newAppointmentModal");
    if (modal && e.target === modal) closeModal("newAppointmentModal");
  });
}
