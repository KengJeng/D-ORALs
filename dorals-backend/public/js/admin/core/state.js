export const state = {
  authToken: localStorage.getItem("admin_token"),
  admin: JSON.parse(localStorage.getItem("admin") || "null"),

  // calendar state
  calendarYear: new Date().getFullYear(),
  calendarMonth: new Date().getMonth() + 1, // 1-12

  // patients pagination
  currentPatientsPage: 1,
  totalPatientsPages: 1,
  totalPatientsCount: 0,

  // audit
  auditCurrentPage: 1,
  auditTotalPages: 1,
  auditFilter: "all",

  // charts registry
  charts: {},
};
