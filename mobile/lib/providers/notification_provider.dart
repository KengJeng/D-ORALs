import 'dart:async';
import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

import '../models/notification_model.dart';

class NotificationProvider with ChangeNotifier {
  final String baseUrl;

  NotificationProvider({this.baseUrl = 'http://127.0.0.1:8000'});

  final List<AppNotification> _notifications = [];
  Timer? _pollingTimer;
  String? _token;

  List<AppNotification> get notifications => List.unmodifiable(_notifications);

  int get unreadCount => _notifications.where((n) => !n.isSent).length;

  Uri _uri(String path) => Uri.parse('$baseUrl$path');

  Map<String, String> get _headers => {
        if (_token != null) 'Authorization': 'Bearer $_token',
        'Accept': 'application/json',
      };

  void setToken(String token) {
    _token = token;
  }

  Future<void> fetchNotifications() async {
    if (_token == null) {
      if (kDebugMode) print('NotificationProvider: token is null');
      return;
    }

    try {
      final response = await http.get(
        _uri('/api/patient/notifications'),
        headers: _headers,
      );

      if (response.statusCode != 200) {
        if (kDebugMode) {
          print('fetchNotifications failed: ${response.statusCode} ${response.body}');
        }
        return;
      }

      final decoded = json.decode(response.body);

      final List list = (decoded is Map && decoded['data'] is List)
          ? (decoded['data'] as List)
          : const [];

      _notifications
        ..clear()
        ..addAll(
          list.map((e) => AppNotification.fromJson(e as Map<String, dynamic>)),
        );

      notifyListeners();
    } catch (e) {
      if (kDebugMode) print('fetchNotifications error: $e');
    }
  }

  Future<void> markAsRead(int notificationId) async {
    if (_token == null) return;

    final idx = _notifications.indexWhere((n) => n.notificationId == notificationId);
    if (idx == -1) return;

    final old = _notifications[idx];

    _notifications[idx] = AppNotification(
      notificationId: old.notificationId,
      appointmentId: old.appointmentId,
      queueNumber: old.queueNumber,
      message: old.message,
      isSent: true,
      createdAt: old.createdAt,
    );
    notifyListeners();

    try {
      final res = await http.patch(
        _uri('/api/patient/notifications/$notificationId/read'),
        headers: _headers,
      );

      if (res.statusCode == 200 || res.statusCode == 204) return;

      _notifications[idx] = old;
      notifyListeners();

      if (kDebugMode) print('markAsRead failed: ${res.statusCode} ${res.body}');
    } catch (e) {
      _notifications[idx] = old;
      notifyListeners();

      if (kDebugMode) print('markAsRead error: $e');
    }
  }

  void startPolling(String token) {
    setToken(token);

    _pollingTimer?.cancel();
    fetchNotifications(); 

    _pollingTimer = Timer.periodic(
      const Duration(seconds: 10),
      (_) => fetchNotifications(),
    );
  }

  void stopPolling() {
    _pollingTimer?.cancel();
    _pollingTimer = null;
  }

  @override
  void dispose() {
    stopPolling();
    super.dispose();
  }
}
