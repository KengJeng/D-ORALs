import { API_BASE } from "./env.js";
import { state } from "./state.js";

export async function apiFetch(path, { method = "GET", body, params } = {}) {
  if (!state.authToken) throw new Error("Missing token");

  const url = new URL(`${API_BASE}${path}`);
  if (params) Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));

  const res = await fetch(url.toString(), {
    method,
    headers: {
      Authorization: `Bearer ${state.authToken}`,
      ...(body ? { "Content-Type": "application/json" } : {}),
    },
    body: body ? JSON.stringify(body) : undefined,
  });

  const ct = res.headers.get("content-type") || "";
  const data = ct.includes("application/json") ? await res.json() : await res.text();

  if (!res.ok) {
    const msg = (data && data.message) ? data.message : `HTTP ${res.status}`;
    throw new Error(msg);
  }
  return data;
}
