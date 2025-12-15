import { apiFetch } from "../core/api.js";
import { state } from "../core/state.js";

export async function loadAuditLogs(page = 1) {
  try {
    state.auditCurrentPage = page;

    const params = { page };
    if (state.auditFilter !== "all") params.filter = state.auditFilter;

    const data = await apiFetch("/audit-logs", { params });

    displayAuditLogs(data);
    updateAuditStats();
  } catch (e) {
    console.error("Error loading audit logs:", e);
    const el = document.getElementById("auditLogsList");
    el && (el.innerHTML = '<tr><td colspan="4" class="text-center py-12 text-red-500">Failed to load audit logs</td></tr>');
  }
}

export function displayAuditLogs(data) {
  const tbody = document.getElementById("auditLogsList");
  if (!tbody) return;

  const logs = data.data || [];
  if (logs.length === 0) {
    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-12 text-gray-400">No audit logs found</td></tr>';
    return;
  }

  tbody.innerHTML = logs.map(log => {
    const actionType = getActionType(log.action);
    const actionColor = getActionColor(actionType);

    return `
      <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
        <td class="px-4 py-4 text-sm text-gray-600">
          <div>${log.log_date}</div>
          <div class="text-xs text-gray-400">${log.log_time}</div>
        </td>
        <td class="px-4 py-4">
          <div class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 text-xs font-bold">
              ${log.user_id}
            </div>
            <span class="text-sm text-gray-700">User #${log.user_id}</span>
          </div>
        </td>
        <td class="px-4 py-4">
          <span class="text-sm text-gray-800">${log.action}</span>
        </td>
        <td class="px-4 py-4">
          <span class="px-3 py-1 rounded-lg text-xs font-medium ${actionColor}">
            ${actionType}
          </span>
        </td>
      </tr>
    `;
  }).join("");

  document.getElementById("auditShowing") && (document.getElementById("auditShowing").textContent = logs.length);
  document.getElementById("auditTotal") && (document.getElementById("auditTotal").textContent = data.total || logs.length);

  state.auditTotalPages = data.last_page || 1;

  const prevBtn = document.getElementById("auditPrevBtn");
  const nextBtn = document.getElementById("auditNextBtn");
  prevBtn && (prevBtn.disabled = state.auditCurrentPage <= 1);
  nextBtn && (nextBtn.disabled = state.auditCurrentPage >= state.auditTotalPages);
}

export function getActionType(action) {
  const a = (action || "").toLowerCase();
  if (a.includes("login") || a.includes("logout")) return "Authentication";
  if (a.includes("appointment")) return "Appointment";
  if (a.includes("patient")) return "Patient";
  if (a.includes("service")) return "Service";
  if (a.includes("queue")) return "Queue";
  return "System";
}

export function getActionColor(type) {
  const colors = {
    Authentication: "bg-blue-100 text-blue-700",
    Appointment: "bg-green-100 text-green-700",
    Patient: "bg-purple-100 text-purple-700",
    Service: "bg-amber-100 text-amber-700",
    Queue: "bg-pink-100 text-pink-700",
    System: "bg-gray-100 text-gray-700",
  };
  return colors[type] || colors.System;
}

export async function updateAuditStats() {
  try {
    const stats = await apiFetch("/audit-logs/stats");
    document.getElementById("auditTodayCount") && (document.getElementById("auditTodayCount").textContent = stats.today_count || 0);
    document.getElementById("auditUserCount") && (document.getElementById("auditUserCount").textContent = stats.user_actions || 0);
    document.getElementById("auditSystemCount") && (document.getElementById("auditSystemCount").textContent = stats.system_events || 0);
    document.getElementById("auditActiveUsers") && (document.getElementById("auditActiveUsers").textContent = stats.active_users || 0);
  } catch (e) {
    console.error("Error loading audit stats:", e);
  }
}

export function filterAuditLogs(value) {
  state.auditFilter = value;
  loadAuditLogs(1);
}
