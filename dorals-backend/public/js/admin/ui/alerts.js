import { dom } from "../core/dom.js";

export function showAlert(msg, type = "success") {
  const el = dom.alert();
  if (!el) return;

  el.className = `mx-8 mt-6 p-4 rounded-xl ${
    type === "success" ? "bg-green-100 text-green-700" : "bg-red-100 text-red-700"
  }`;
  el.textContent = msg;
  el.classList.remove("hidden");

  setTimeout(() => el.classList.add("hidden"), 5000);
}
