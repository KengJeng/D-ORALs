class Service {
  final int serviceId;
  final String name;
  final int duration;

  Service({
    required this.serviceId,
    required this.name,
    required this.duration,
  });

  factory Service.fromJson(Map<String, dynamic> json) {
    return Service(
      serviceId: json['service_id'] is int
          ? json['service_id'] as int
          : int.parse(json['service_id'].toString()),
      name: json['name'] as String,
      duration: json['duration'] is int
          ? json['duration'] as int
          : int.parse(json['duration'].toString()),
    );
  }
}
