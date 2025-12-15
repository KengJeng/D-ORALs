import { apiFetch } from "../core/api.js";
import { dom } from "../core/dom.js";
import { showAlert } from "../ui/alerts.js";

export async function loadQueue() {
  try {
    const apts = await apiFetch("/appointments/today-queue");
    const list = dom.queueList();
    if (!list) return;

    if (!Array.isArray(apts) || apts.length === 0) {
      list.innerHTML = '<p class="text-gray-400 text-center py-12">No appointments in queue</p>';
      return;
    }

    list.innerHTML = apts.map(a => `
      <div class="flex items-center justify-between p-5 border border-gray-100 rounded-xl hover:shadow-md transition bg-white">
        <div class="flex items-center space-x-4">
          <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
            <span class="text-white font-bold text-xl">${a.queue_number}</span>
          </div>
          <div>
            <p class="font-semibold text-gray-800 text-lg">${a.patient.first_name} ${a.patient.last_name}</p>
            <p class="text-sm text-gray-500">${a.services?.map(s => s.name).join(', ') || 'No services'}</p>
          </div>
        </div>

        <div class="flex items-center space-x-3">
          ${a.google_calendar_url ? `
            <a href="${a.google_calendar_url}" target="_blank"
              class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold
                     bg-emerald-50 text-emerald-700 border border-emerald-100 hover:bg-emerald-100 transition">
              <i class="fa-brands fa-google text-sm"></i>
              Google Calendar
            </a>
          ` : ""}

          ${a.status !== "Completed" ? `
            <button data-action="complete" data-id="${a.appointment_id}"
              class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-xl text-sm font-medium transition shadow-sm">
              Complete
            </button>
          ` : ""}
        </div>
      </div>
    `).join("");
  } catch (e) {
    console.error("Error loading queue:", e);
  }
}

export async function loadNextPatient() {
  try {
    const next = await apiFetch("/queue/next");
    const box = dom.nextPatient();
    if (!box) return;

    if (!next || !next.appointment_id) {
      box.className = "flex items-center justify-center min-h-[110px]";
      box.innerHTML = `
        <div class="text-center">
          <p class="text-sm font-semibold text-gray-700">No patients in queue</p>
          <p class="text-xs text-gray-500 mt-1">Waiting queue is empty.</p>
        </div>
      `;
      return;
    }

    const queueNo = next.queue_number ?? "--";
    const full = `${next.patient?.first_name ?? ""} ${next.patient?.last_name ?? ""}`.trim() || "Unnamed Patient";
    const services = Array.isArray(next.services) && next.services.length
      ? next.services.map(s => s?.name).filter(Boolean).join(", ")
      : "No services";

    box.className = "w-full";
    box.innerHTML = `
      <div class="w-full">
        <div class="flex items-start justify-between gap-6">
          <div class="flex items-start gap-4 min-w-0">
            <div class="shrink-0 w-14 h-14 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center">
              <div class="text-center leading-none">
                <div class="text-[11px] font-semibold text-blue-700">QUEUE</div>
                <div class="text-2xl font-extrabold text-blue-800">#${queueNo}</div>
              </div>
            </div>
            <div class="min-w-0">
              <p class="text-lg font-extrabold text-gray-900 truncate">${full}</p>
              <p class="text-sm text-gray-600 mt-1 truncate">${services}</p>
            </div>
          </div>

          <div class="shrink-0 text-xs font-semibold px-3 py-2 rounded-xl bg-amber-50 text-amber-700 border border-amber-100">
            Ready to be called
          </div>
        </div>
      </div>
    `;
  } catch (e) {
    console.error("Error loading next:", e);
  }
}

export async function updateStatus(id, status) {
  try {
    await apiFetch(`/appointments/${id}`, { method: "PATCH", body: { status } });
    showAlert(`Marked as ${status}`, "success");
  } catch (e) {
    showAlert(e.message || "Failed to update", "error");
  }
}

export async function callNextPatient() {
  try {
    const result = await apiFetch("/queue/call-next", { method: "POST" });

    if (result?.patient) {
      const p = result.patient.patient || result.patient;
      const name = `${p.first_name || ""} ${p.last_name || ""}`.trim();

      showAlert(`Called: ${name || "Patient"}`, "success");

      // refresh UI
      await Promise.all([loadQueue(), loadNextPatient()]);
      return;
    }

    showAlert(result?.message || "No patients", "error");
  } catch (e) {
    console.error("Error calling next patient:", e);
    showAlert(e.message || "Error calling patient", "error");
  }
}

