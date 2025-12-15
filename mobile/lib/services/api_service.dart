import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/patient.dart';
import '../models/appointment.dart';
import '../models/service.dart';

const String apiBase = 'http://localhost:8000/api';


class ApiService {
  // Helper: Common headers with auth
  static Map<String, String> _authHeaders(String token) {
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    };
  }

  // ---------- Auth Methods ----------

  // Login
  static Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    final uri = Uri.parse('$apiBase/patient/login');
    final response = await http.post(
      uri,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body) as Map<String, dynamic>;
    } else {
      final body = response.body.isNotEmpty ? jsonDecode(response.body) : {};
      throw Exception(
          body['error'] ?? 'Login failed (code ${response.statusCode})');
    }
  }

  // Register
  static Future<Map<String, dynamic>> register({
    required String firstName,
    required String middleName,
    required String lastName,
    required String sex,
    required String contactNo,
    required String address,
    required String email,
    required String password,
    required String confirmPassword,
  }) async {
    final uri = Uri.parse('$apiBase/patient/register');
    final response = await http.post(
      uri,
      headers: const {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'first_name': firstName,
        'middle_name': middleName,
        'last_name': lastName,
        'sex': sex,
        'contact_no': contactNo,
        'address': address,
        'email': email,
        'password': password,
        'password_confirmation': confirmPassword,
      }),
    );

    if (response.statusCode == 200 || response.statusCode == 201) {
      return response.body.isNotEmpty
          ? jsonDecode(response.body) as Map<String, dynamic>
          : <String, dynamic>{};
    } else {
      Map<String, dynamic> body = {};
      try {
        if (response.body.isNotEmpty) {
          body = jsonDecode(response.body) as Map<String, dynamic>;
        }
      } catch (_) {
        // ignore JSON parse error
      }

      String message = 'Registration failed (code ${response.statusCode})';

      if (body['message'] is String) {
        message = body['message'];
      } else if (body['error'] is String) {
        message = body['error'];
      } else if (body['errors'] is Map) {
        // Laravel validation errors
        final errors = body['errors'] as Map<String, dynamic>;
        final parts = <String>[];
        errors.forEach((key, value) {
          if (value is List && value.isNotEmpty) {
            parts.add(value.first.toString());
          } else {
            parts.add(value.toString());
          }
        });
        if (parts.isNotEmpty) {
          message = parts.join('\n');
        }
      }

      throw Exception(message);
    }
  }

  // ---------- Patient Methods ----------

  // Fetch Profile
  static Future<Patient> getProfile(String token) async {
    final uri = Uri.parse('$apiBase/patient/profile');
    final response = await http.get(
      uri,
      headers: _authHeaders(token),
    );

    if (response.statusCode == 200) {
      return Patient.fromJson(jsonDecode(response.body));
    } else {
      throw Exception('Failed to load profile (code ${response.statusCode})');
    }
  }

  // ---------- Service Methods ----------

  // Fetch Services
  static Future<List<Service>> getServices(String token) async {
    final uri = Uri.parse('$apiBase/services');
    final response = await http.get(
      uri,
      headers: _authHeaders(token),
    );

    if (response.statusCode == 200) {
      final List<dynamic> data = jsonDecode(response.body);
      return data
          .map((json) => Service.fromJson(json as Map<String, dynamic>))
          .toList();
    } else {
      throw Exception(
          'Failed to load services (code ${response.statusCode}, body: ${response.body})');
    }
  }

  // ---------- Appointment Methods ----------

  // Create Appointment
static Future<Map<String, dynamic>> createAppointment({
  required String token,
  required int patientId,
  required String scheduledDate,
  required List<int> serviceIds,
}) async {
  final uri = Uri.parse('$apiBase/appointments');

  final payload = {
    'patient_id': patientId,
    'scheduled_date': scheduledDate,
    'service_ids': serviceIds,
  };

  final response = await http.post(
    uri,
    headers: _authHeaders(token),
    body: jsonEncode(payload),
  );

  if (response.statusCode != 201) {

    // try to show backend message if JSON
    try {
      final decoded = response.body.isNotEmpty ? jsonDecode(response.body) : {};
      final msg = (decoded is Map && decoded['message'] != null)
          ? decoded['message'].toString()
          : 'Failed to create appointment (code ${response.statusCode})';
      throw Exception(msg);
    } catch (_) {
      throw Exception('Failed to create appointment (code ${response.statusCode})');
    }
  }

  return jsonDecode(response.body) as Map<String, dynamic>;
}


  // Get Appointments (My Appointments)
  static Future<List<Appointment>> getAppointments(String token) async {
    final uri = Uri.parse('$apiBase/appointments/my');
    final response = await http.get(
      uri,
      headers: _authHeaders(token),
    );

    if (response.statusCode == 200) {
      final decoded = jsonDecode(response.body);
      final List<dynamic> data =
          decoded is List ? decoded : (decoded['data'] as List<dynamic>);
      return data.map((json) => Appointment.fromJson(json)).toList();
    } else {
      throw Exception('Failed to load appointments (code ${response.statusCode})');
    }
  }

  // Get Appointment by ID
  static Future<Appointment> getAppointmentById({
    required String token,
    required int appointmentId,
  }) async {
    final uri = Uri.parse('$apiBase/appointments/$appointmentId');
    final response = await http.get(
      uri,
      headers: _authHeaders(token),
    );

    if (response.statusCode == 200) {
      return Appointment.fromJson(jsonDecode(response.body));
    } else {
      throw Exception('Failed to load appointment (code ${response.statusCode})');
    }
  }

  // ---------- Dashboard Methods ----------

  // Get Dashboard Stats
  static Future<Map<String, dynamic>> getDashboardStats(String token) async {
    final uri = Uri.parse('$apiBase/dashboard/stats');
    final response = await http.get(
      uri,
      headers: _authHeaders(token),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body) as Map<String, dynamic>;
    } else {
      throw Exception('Failed to load dashboard stats (code ${response.statusCode})');
    }
  }

  // Get Upcoming Appointments
  static Future<List<Appointment>> getUpcomingAppointments(String token) async {
    final uri = Uri.parse('$apiBase/dashboard/upcoming');
    final response = await http.get(
      uri,
      headers: _authHeaders(token),
    );

    if (response.statusCode == 200) {
      final List<dynamic> data = jsonDecode(response.body);
      return data.map((json) => Appointment.fromJson(json)).toList();
    } else {
      throw Exception(
          'Failed to load upcoming appointments (code ${response.statusCode})');
    }
  }
  
  // Export Appointments
  static Future<void> exportAppointments(String token) async {
    final uri = Uri.parse('$apiBase/appointments/export');
    
    final response = await http.get(
      uri,
      headers: {
        'Authorization': 'Bearer $token',
      },
    );

    if (response.statusCode == 200) {
      // Handle the export success
      print('Export successful!');
    } else {
      throw Exception('Failed to export appointments');
    }
  }

}
