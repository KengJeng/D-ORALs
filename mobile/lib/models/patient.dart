class Patient {
  final int patientId;
  final String firstName;
  final String? middleName;
  final String lastName;
  final String sex;
  final String contactNo;
  final String address;
  final String email;
  final String createdAt;

  Patient({
    required this.patientId,
    required this.firstName,
    this.middleName,
    required this.lastName,
    required this.sex,
    required this.contactNo,
    required this.address,
    required this.email,
    required this.createdAt,
  });

  factory Patient.fromJson(Map<String, dynamic> json) {
    return Patient(
      patientId: json['patient_id'],
      firstName: json['first_name'],
      middleName: json['middle_name'],
      lastName: json['last_name'],
      sex: json['sex'],
      contactNo: json['contact_no'],
      address: json['address'],
      email: json['email'],
      createdAt: json['created_at'],
    );
  }

  String get fullName {
  return [
    firstName,
    if (middleName != null && middleName!.isNotEmpty) middleName,
    lastName,
  ].join(' ');
}

}