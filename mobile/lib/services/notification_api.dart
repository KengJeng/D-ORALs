import 'dart:convert';
import 'package:http/http.dart' as http;

// IMPORTANT: import your model here
import '../models/notification_model.dart';

class NotificationApi {
  final String baseUrl;
  final Map<String, String> _headers;

  NotificationApi({
    required this.baseUrl,
    required String token,
  }) : _headers = {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        };

  Future<List<AppNotification>> fetchNotifications() async {
    final url = Uri.parse('$baseUrl/api/patient/notifications');
    final res = await http.get(url, headers: _headers);

    if (res.statusCode != 200) {
      throw Exception('Failed to fetch notifications: ${res.body}');
    }

    final decoded = jsonDecode(res.body) as List;
    return decoded.map((e) => AppNotification.fromJson(e)).toList();
  }

  Future<void> markRead(int notificationId) async {
    final url = Uri.parse('$baseUrl/api/patient/notifications/$notificationId/read');

    // You can use POST or PATCH depending on your backend route
    final res = await http.post(url, headers: _headers);

    if (res.statusCode != 200 && res.statusCode != 204) {
      throw Exception('Failed to mark read: ${res.body}');
    }
  }
}
