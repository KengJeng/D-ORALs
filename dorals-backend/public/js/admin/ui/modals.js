export function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove("hidden");
}

export function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add("hidden");
}
