import { apiFetch } from "../core/api.js";
import { showAlert } from "../ui/alerts.js";

let allServices = [];

export async function displayServices() {
  try {
    allServices = await apiFetch("/services");

    const box = document.getElementById("servicesList");
    if (!box) return;

    if (!Array.isArray(allServices) || allServices.length === 0) {
      box.innerHTML = '<p class="text-gray-400 text-center py-12">No services</p>';
      return;
    }

    box.innerHTML = allServices.map(s => `
      <div class="border border-gray-100 rounded-xl p-5 hover:shadow-md transition bg-white">
        <div class="flex justify-between items-center">
          <div>
            <p class="font-bold text-lg text-gray-800">${s.name}</p>
            <p class="text-gray-500 text-sm mt-1">Duration: ${s.duration} minutes</p>
          </div>
          <div class="flex items-center space-x-2">
            <button data-action="service-edit" data-id="${s.service_id}"
              class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-sm transition">Edit</button>
            <button data-action="service-delete" data-id="${s.service_id}"
              class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm transition">Delete</button>
          </div>
        </div>
      </div>
    `).join("");
  } catch (e) {
    console.error("Error loading services:", e);
  }
}

export async function editService(id) {
  const svc = allServices.find(s => s.service_id === Number(id));
  if (!svc) return;

  const name = prompt("Service Name:", svc.name);
  const dur = prompt("Duration (min):", svc.duration);

  if (!name || !dur) return;

  try {
    await apiFetch(`/services/${id}`, { method: "PATCH", body: { name, duration: parseInt(dur) } });
    showAlert("Updated", "success");
    displayServices();
  } catch (e) {
    showAlert(e.message || "Failed", "error");
  }
}

export async function deleteService(id) {
  if (!confirm("Delete this service?")) return;

  try {
    await apiFetch(`/services/${id}`, { method: "DELETE" });
    showAlert("Deleted", "success");
    displayServices();
  } catch (e) {
    showAlert(e.message || "Failed", "error");
  }
}
