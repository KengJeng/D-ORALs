class Appointment {
  final int appointmentId;
  final int patientId;
  final String scheduledDate;    
  final String status;
  final int? queueNumber;
  final String createdAt;
  final List<String> servicesList; 
  Appointment({
    required this.appointmentId,
    required this.patientId,
    required this.scheduledDate,
    required this.status,
    required this.createdAt,
    this.queueNumber,
    required this.servicesList,
  });

  factory Appointment.fromJson(Map<String, dynamic> json) {
    return Appointment(
      appointmentId: json['appointment_id'] as int,
      patientId: json['patient_id'] as int,
      scheduledDate: json['scheduled_date'] as String,
      status: (json['status'] ?? '').toString(),
      queueNumber: json['queue_number'] as int?,
      createdAt: json['created_at'] as String,
      servicesList: (json['services'] as List<dynamic>? ?? [])
          .map((service) => service['name'].toString())
          .toList(),
    );
  }
}
