import 'package:flutter/material.dart';
import '../models/appointment.dart';
import '../models/service.dart';
import '../services/api_service.dart';


class AppointmentProvider with ChangeNotifier {
  List<Appointment> _appointments = [];
  List<Service> _services = [];
  Map<String, dynamic>? _dashboardStats;
  List<Appointment> _upcomingAppointments = [];
  bool _isLoading = false;



  List<Appointment> get appointments => _appointments;
  List<Service> get services => _services;
  Map<String, dynamic>? get dashboardStats => _dashboardStats;
  List<Appointment> get upcomingAppointments => _upcomingAppointments;
  bool get isLoading => _isLoading;
  // -------------------------------
  // Load all patient services
  // -------------------------------
Future<void> loadServices(String token) async {
  _isLoading = true;
  notifyListeners();

  try {
    _services = await ApiService.getServices(token);
  } finally {
    _isLoading = false;
    notifyListeners();
  }
}


  // -------------------------------
  // Load all patient's appointments
  // -------------------------------
  Future<void> loadAppointments(String token) async {
    _setLoading(true);

    try {
      _appointments = await ApiService.getAppointments(token);
    } finally {
      _setLoading(false);
    }
  }

  // -------------------------------
  // Create appointment then refresh the list
  // -------------------------------
  Future<void> createAppointment({
  required String token,
  required int patientId,
  required String scheduledDate,
  required List<int> serviceIds,
}) async {
  _setLoading(true);
  try {
    await ApiService.createAppointment(
      token: token,
      patientId: patientId,
      scheduledDate: scheduledDate,
      serviceIds: serviceIds,
    );
    await loadAppointments(token);
  } finally {
    _setLoading(false);
  }
}

  // -------------------------------
  // Export Appointments
  // -------------------------------
Future<void> exportReports(String token) async {
    _isLoading = true;
    notifyListeners();

    try {
      await ApiService.exportAppointments(token);  
    } catch (e) {
      print("Error exporting appointments: $e");
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }


  // -------------------------------
  // Load dashboard + upcoming appointments
  // -------------------------------
  Future<void> loadDashboardData(String token) async {
    
    _setLoading(true);

    try {
      _dashboardStats = await ApiService.getDashboardStats(token);
      _upcomingAppointments =
          await ApiService.getUpcomingAppointments(token);
    } finally {
      _setLoading(false);
    }
  }

  // -------------------------------
  // Helper: Update loading state
  // -------------------------------
  void _setLoading(bool value) {
    _isLoading = value;
    notifyListeners();
  }

}
