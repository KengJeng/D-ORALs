class AppNotification {
  final int notificationId;
  final int appointmentId;
  final int? queueNumber;
  final String message;
  final bool isSent;
  final DateTime createdAt;

  AppNotification({
    required this.notificationId,
    required this.appointmentId,
    required this.message,
    required this.isSent,
    required this.createdAt,
    this.queueNumber,
  });

  factory AppNotification.fromJson(Map<String, dynamic> json) {
    return AppNotification(
      notificationId: (json['notification_id'] ?? json['notificationId']) as int,
      appointmentId: (json['appointment_id'] ?? json['appointmentId']) as int,
      queueNumber: json['queue_number'] ?? json['queueNumber'],
      message: (json['message'] ?? '') as String,
      isSent: (json['is_sent'] ?? json['isSent'] ?? false) == true ||
          (json['is_sent'] ?? 0) == 1,
      createdAt: DateTime.parse(
        (json['created_at'] ?? json['createdAt']).toString(),
      ),
    );
  }

  AppNotification copyWith({
    int? notificationId,
    int? appointmentId,
    int? queueNumber,
    String? message,
    bool? isSent,
    DateTime? createdAt,
  }) {
    return AppNotification(
      notificationId: notificationId ?? this.notificationId,
      appointmentId: appointmentId ?? this.appointmentId,
      queueNumber: queueNumber ?? this.queueNumber,
      message: message ?? this.message,
      isSent: isSent ?? this.isSent,
      createdAt: createdAt ?? this.createdAt,
    );
  }

  String get createdAtText {
    final y = createdAt.year.toString().padLeft(4, '0');
    final m = createdAt.month.toString().padLeft(2, '0');
    final d = createdAt.day.toString().padLeft(2, '0');
    final hh = createdAt.hour.toString().padLeft(2, '0');
    final mm = createdAt.minute.toString().padLeft(2, '0');
    return '$y-$m-$d  $hh:$mm';
  }
}
