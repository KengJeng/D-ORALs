import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/patient.dart';
import '../services/api_service.dart';

class AuthProvider with ChangeNotifier {
  String? _token;
  Patient? _patient;
  bool _isLoading = false;

  String? get token => _token;
  Patient? get patient => _patient;
  bool get isLoading => _isLoading;
  bool get isAuthenticated => _token != null;

  AuthProvider() {
    _loadToken();
  }

  Future<void> _loadToken() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('token');

    if (_token != null) {
      try {
        _patient = await ApiService.getProfile(_token!);  
        notifyListeners();
      } catch (e) {
        await logout(); 
      }
    }
  }

  // Login
  Future<void> login(String email, String password) async {
    _isLoading = true;
    notifyListeners();

    try {
      final response = await ApiService.login(email: email, password: password);
      _token = response['token'];
      _patient = Patient.fromJson(response['patient']);

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', _token!);  

      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      rethrow;  
    }
  }

  // Register
  Future<void> register({
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
    _isLoading = true;
    notifyListeners();

    try {
      await ApiService.register(
        firstName: firstName,
        middleName: middleName,
        lastName: lastName,
        sex: sex,
        contactNo: contactNo,
        address: address,
        email: email,
        password: password,
        confirmPassword: confirmPassword,
      );

      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _isLoading = false;
      notifyListeners();
      rethrow;
    }
  }

  // Logout
  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    _token = null;
    _patient = null;
    notifyListeners();
  }

  // Refresh Profile
  Future<void> refreshProfile() async {
    if (_token != null) {
      try {
        _patient = await ApiService.getProfile(_token!); 
        notifyListeners();
      } catch (e) {
        print("Error refreshing profile: $e");
      }
    }
  }
}
