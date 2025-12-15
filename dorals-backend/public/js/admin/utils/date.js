export function formatDateForApi(date) {
  return date.toISOString().split("T")[0]; // YYYY-MM-DD
}

export function getExportRange(rangeKey) {
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  if (rangeKey === "today") {
    return { from: formatDateForApi(today), to: formatDateForApi(today) };
  }

  if (rangeKey === "last7") {
    const from = new Date(today);
    from.setDate(from.getDate() - 6);
    return { from: formatDateForApi(from), to: formatDateForApi(today) };
  }

  if (rangeKey === "thisMonth") {
    const from = new Date(today.getFullYear(), today.getMonth(), 1);
    const to = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    return { from: formatDateForApi(from), to: formatDateForApi(to) };
  }

  return { from: null, to: null };
}
