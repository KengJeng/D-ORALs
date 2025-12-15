export const dom = {
  sidebarAdminName: () => document.getElementById("sidebarAdminName"),
  pageTitle: () => document.getElementById("pageTitle"),
  alert: () => document.getElementById("alert"),

  // dashboard stats
  todayQueue: () => document.getElementById("todayQueue"),
  completedToday: () => document.getElementById("completedToday"),
  totalPatients: () => document.getElementById("totalPatients"),
  pendingToday: () => document.getElementById("pendingToday"),

  // queue
  queueList: () => document.getElementById("queueList"),
  nextPatient: () => document.getElementById("nextPatient"),

  // calendar
  calendarMonthLabel: () => document.getElementById("calendarMonthLabel"),
  appointmentCalendar: () => document.getElementById("appointmentCalendar"),
  calendarRoot: () => document.getElementById("calendarRoot"), // optional wrapper div if you add it

  // clock
  currentTimeDisplay: () => document.getElementById("currentTimeDisplay"),
};
