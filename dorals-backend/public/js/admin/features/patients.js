import { apiFetch } from "../core/api.js";
import { state } from "../core/state.js";
import { showAlert } from "../ui/alerts.js";
import { openModal, closeModal } from "../ui/modals.js";

let allPatients = [];
let searchTimeout = null;

export async function displayPatients(page = 1) {
  try {
    const data = await apiFetch("/patients", { params: { page } });

    allPatients = data.data || [];
    state.currentPatientsPage = data.current_page || 1;
    state.totalPatientsPages = data.last_page || 1;
    state.totalPatientsCount = data.total || allPatients.length;

    const box = document.getElementById("patientsList");
    if (!box) return;

    if (allPatients.length === 0) {
      box.innerHTML = '<p class="text-gray-400 text-center py-12">No patients</p>';
      updatePatientsPagination();
      return;
    }

    box.innerHTML = allPatients.map(p => `
      <div class="border border-gray-100 rounded-xl p-5 hover:shadow-md transition bg-white">
        <div class="flex justify-between items-start">
          <div>
            <p class="font-bold text-lg text-gray-800">
              ${p.first_name} ${p.middle_name || ""} ${p.last_name}
              <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-sm font-medium">${p.sex}</span>
            </p>
            <p class="text-gray-600 text-sm mt-1">${p.email || ""}</p>
            <p class="text-gray-600 text-sm">${p.contact_no || ""}</p>
          </div>

          <div class="flex items-center gap-2">
            <button data-action="patient-edit" data-id="${p.patient_id}"
              class="px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-sm">Edit</button>
            <button data-action="patient-delete" data-id="${p.patient_id}"
              class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm">Delete</button>
          </div>
        </div>
        <p class="text-gray-500 text-sm mt-3">${p.address || ""}</p>
      </div>
    `).join("");

    updatePatientsPagination();
    const pag = document.getElementById("patientsPagination");
    pag && pag.classList.remove("hidden");
  } catch (e) {
    console.error("Error loading patients:", e);
  }
}

export function updatePatientsPagination() {
  const from = (state.currentPatientsPage - 1) * 20 + 1;
  const to = Math.min(state.currentPatientsPage * 20, state.totalPatientsCount);

  const fromEl = document.getElementById("patientsFrom");
  const toEl = document.getElementById("patientsTo");
  const totalEl = document.getElementById("patientsTotal");

  fromEl && (fromEl.textContent = state.totalPatientsCount ? from : 0);
  toEl && (toEl.textContent = state.totalPatientsCount ? to : 0);
  totalEl && (totalEl.textContent = state.totalPatientsCount || 0);

  const prevBtn = document.getElementById("patientsPrevBtn");
  const nextBtn = document.getElementById("patientsNextBtn");
  prevBtn && (prevBtn.disabled = state.currentPatientsPage === 1);
  nextBtn && (nextBtn.disabled = state.currentPatientsPage === state.totalPatientsPages);

  const pageNumbersDiv = document.getElementById("patientsPageNumbers");
  if (!pageNumbersDiv) return;

  let pageNumbers = "";
  for (let i = 1; i <= state.totalPatientsPages; i++) {
    if (i === 1 || i === state.totalPatientsPages || (i >= state.currentPatientsPage - 1 && i <= state.currentPatientsPage + 1)) {
      const activeClass = i === state.currentPatientsPage ? "bg-blue-500 text-white" : "bg-white text-gray-700 hover:bg-gray-50";
      pageNumbers += `
        <button data-action="patients-page" data-page="${i}"
          class="px-4 py-2 border border-gray-300 rounded-lg ${activeClass}">
          ${i}
        </button>
      `;
    } else if (i === state.currentPatientsPage - 2 || i === state.currentPatientsPage + 2) {
      pageNumbers += '<span class="px-2 py-2">...</span>';
    }
  }
  pageNumbersDiv.innerHTML = pageNumbers;
}

export function goToPatientsPage(directionOrNumber) {
  let newPage = state.currentPatientsPage;

  if (directionOrNumber === "prev") newPage = Math.max(1, state.currentPatientsPage - 1);
  else if (directionOrNumber === "next") newPage = Math.min(state.totalPatientsPages, state.currentPatientsPage + 1);
  else newPage = Number(directionOrNumber);

  displayPatients(newPage);
}

export function searchPatients() {
  const input = document.getElementById("patientSearch");
  const search = (input?.value || "").toLowerCase().trim();

  if (searchTimeout) clearTimeout(searchTimeout);

  searchTimeout = setTimeout(() => {
    if (!search) {
      displayPatients(1);
      const pag = document.getElementById("patientsPagination");
      pag && pag.classList.remove("hidden");
    } else {
      searchPatientsAPI(search);
    }
  }, 300);
}

export async function searchPatientsAPI(searchQuery) {
  try {
    const box = document.getElementById("patientsList");
    if (box) box.innerHTML = '<p class="text-gray-400 text-center py-12">Searching...</p>';

    const pag = document.getElementById("patientsPagination");
    pag && pag.classList.add("hidden");

    const data = await apiFetch("/patients", { params: { search: searchQuery } });
    const results = data.data || [];

    if (!box) return;

    if (results.length === 0) {
      box.innerHTML = '<p class="text-gray-400 text-center py-12">No patients found</p>';
      return;
    }

    box.innerHTML = results.map(p => `
      <div class="border border-gray-100 rounded-xl p-5 hover:shadow-md transition bg-white">
        <div class="flex justify-between items-start">
          <div>
            <p class="font-bold text-lg text-gray-800">${p.first_name} ${p.middle_name || ""} ${p.last_name}</p>
            <p class="text-gray-600 text-sm mt-1">${p.email || ""}</p>
            <p class="text-gray-600 text-sm">${p.contact_no || ""}</p>
          </div>
          <div class="flex items-center gap-2">
            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-sm font-medium">${p.sex}</span>
            <button data-action="patient-edit" data-id="${p.patient_id}"
              class="px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-sm">Edit</button>
            <button data-action="patient-delete" data-id="${p.patient_id}"
              class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm">Delete</button>
          </div>
        </div>
        <p class="text-gray-500 text-sm mt-3">${p.address || ""}</p>
      </div>
    `).join("");
  } catch (e) {
    console.error("Error searching patients:", e);
    const box = document.getElementById("patientsList");
    box && (box.innerHTML = '<p class="text-red-500 text-center py-12">Error searching patients</p>');
  }
}

export async function openEditPatientModal(patientId) {
  try {
    let patient = allPatients.find(p => p.patient_id === Number(patientId));

    if (!patient) {
      patient = await apiFetch(`/patients/${patientId}`);
    }

    populateEditModal(patient);
    openModal("editPatientModal");
  } catch (e) {
    console.error(e);
    showAlert("Failed to open edit modal", "error");
  }
}

function populateEditModal(p) {
  document.getElementById("editPatientId").value = p.patient_id || p.id || "";
  document.getElementById("editFirstName").value = p.first_name || "";
  document.getElementById("editMiddleName").value = p.middle_name || "";
  document.getElementById("editLastName").value = p.last_name || "";
  document.getElementById("editEmail").value = p.email || "";
  document.getElementById("editContact").value = p.contact_no || "";
  document.getElementById("editSex").value = p.sex || "Male";
  document.getElementById("editAddress").value = p.address || "";
}

export async function submitEditPatient() {
  const id = document.getElementById("editPatientId").value;

  const payload = {
    first_name: document.getElementById("editFirstName").value,
    middle_name: document.getElementById("editMiddleName").value,
    last_name: document.getElementById("editLastName").value,
    email: document.getElementById("editEmail").value,
    contact_no: document.getElementById("editContact").value,
    sex: document.getElementById("editSex").value,
    address: document.getElementById("editAddress").value,
  };

  try {
    await apiFetch(`/patients/${id}`, { method: "PATCH", body: payload });
    closeModal("editPatientModal");
    showAlert("Patient updated", "success");
    displayPatients(state.currentPatientsPage);
  } catch (e) {
    console.error(e);
    showAlert(e.message || "Failed to update patient", "error");
  }
}

export async function deletePatient(id) {
  try {
    await apiFetch(`/patients/${id}`, { method: "DELETE" });
    showAlert("Patient deleted", "success");
    displayPatients(state.currentPatientsPage);
  } catch (e) {
    console.error(e);
    showAlert(e.message || "Failed to delete patient", "error");
  }
}

export function confirmDeletePatient(id) {
  if (!confirm("Are you sure you want to delete this patient? This action cannot be undone.")) return;
  deletePatient(id);
}
