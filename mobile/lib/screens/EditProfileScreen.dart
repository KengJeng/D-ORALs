import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:http/http.dart' as http;

import '../providers/auth_provider.dart';

class EditProfileScreen extends StatefulWidget {
  final dynamic patient; // replace with your Patient model type if available

  const EditProfileScreen({super.key, required this.patient});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final _formKey = GlobalKey<FormState>();

  late final TextEditingController _firstNameController;
  late final TextEditingController _middleNameController;
  late final TextEditingController _lastNameController;
  late final TextEditingController _emailController;
  late final TextEditingController _contactNoController;
  late final TextEditingController _addressController;

  bool _isLoading = false;

  // Sex dropdown
  final List<String> _sexOptions = const ['Male', 'Female'];
  String? _selectedSex;

  @override
  void initState() {
    super.initState();

    // Adjust these field names depending on your Patient model JSON mapping
    _firstNameController = TextEditingController(text: widget.patient.firstName ?? '');
    _middleNameController = TextEditingController(text: widget.patient.middleName ?? '');
    _lastNameController = TextEditingController(text: widget.patient.lastName ?? '');
    _emailController = TextEditingController(text: widget.patient.email ?? '');
    _contactNoController = TextEditingController(text: widget.patient.contactNo ?? '');
    _addressController = TextEditingController(text: widget.patient.address ?? '');

    final sex = (widget.patient.sex ?? '').toString();
    _selectedSex = _sexOptions.contains(sex) ? sex : null;
  }

  @override
  void dispose() {
    _firstNameController.dispose();
    _middleNameController.dispose();
    _lastNameController.dispose();
    _emailController.dispose();
    _contactNoController.dispose();
    _addressController.dispose();
    super.dispose();
  }

  Future<void> _saveProfile(String token) async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      final uri = Uri.parse('http://localhost:8000/api/patient/profile');

      final middle = _middleNameController.text.trim();

      final payload = {
        'first_name': _firstNameController.text.trim(),
        'middle_name': middle.isEmpty ? null : middle,
        'last_name': _lastNameController.text.trim(),
        'email': _emailController.text.trim(),
        'contact_no': _contactNoController.text.trim(),
        'address': _addressController.text.trim().isEmpty ? null : _addressController.text.trim(),
        if (_selectedSex != null) 'sex': _selectedSex,
      };

      final res = await http.put(
        uri,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode(payload),
      );

      if (!mounted) return;

      if (res.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Profile updated successfully')),
        );
        Navigator.pop(context, true); // return success to refresh profile if needed
      } else {
        // show backend validation errors if any
        String msg = 'Failed to update profile (code ${res.statusCode})';
        try {
          final decoded = jsonDecode(res.body);
          if (decoded is Map && decoded['message'] != null) {
            msg = decoded['message'].toString();
          }
          if (decoded is Map && decoded['errors'] is Map) {
            final errors = decoded['errors'] as Map<String, dynamic>;
            final parts = <String>[];
            errors.forEach((k, v) {
              if (v is List && v.isNotEmpty) parts.add(v.first.toString());
            });
            if (parts.isNotEmpty) msg = parts.join('\n');
          }
        } catch (_) {
          // ignore parse errors
        }

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(msg)),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  InputDecoration _dec(String label) {
    return InputDecoration(
      labelText: label,
      filled: true,
      fillColor: Colors.white,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: BorderSide.none,
      ),
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final token = auth.token;

    if (token == null) {
      return const Scaffold(
        body: Center(child: Text('No valid session found. Please login again.')),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Edit Profile'),
        centerTitle: true,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Form(
            key: _formKey,
            child: Column(
              children: [
                // First name
                TextFormField(
                  controller: _firstNameController,
                  decoration: _dec('First Name'),
                  textInputAction: TextInputAction.next,
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'First name is required' : null,
                ),
                const SizedBox(height: 12),

                // Middle name
                TextFormField(
                  controller: _middleNameController,
                  decoration: _dec('Middle Name (optional)'),
                  textInputAction: TextInputAction.next,
                ),
                const SizedBox(height: 12),

                // Last name
                TextFormField(
                  controller: _lastNameController,
                  decoration: _dec('Last Name'),
                  textInputAction: TextInputAction.next,
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Last name is required' : null,
                ),
                const SizedBox(height: 12),

                // Email
                TextFormField(
                  controller: _emailController,
                  decoration: _dec('Email'),
                  keyboardType: TextInputType.emailAddress,
                  textInputAction: TextInputAction.next,
                  validator: (v) {
                    final val = (v ?? '').trim();
                    if (val.isEmpty) return 'Email is required';
                    if (!val.contains('@')) return 'Enter a valid email';
                    return null;
                  },
                ),
                const SizedBox(height: 12),

                // Contact No
                TextFormField(
                  controller: _contactNoController,
                  decoration: _dec('Contact No.'),
                  keyboardType: TextInputType.phone,
                  textInputAction: TextInputAction.next,
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Contact number is required' : null,
                ),
                const SizedBox(height: 12),

                // Sex dropdown
                DropdownButtonFormField<String>(
                  value: _selectedSex,
                  decoration: _dec('Sex'),
                  items: _sexOptions
                      .map((s) => DropdownMenuItem(value: s, child: Text(s)))
                      .toList(),
                  onChanged: (v) => setState(() => _selectedSex = v),
                  validator: (v) => (v == null || v.isEmpty) ? 'Sex is required' : null,
                ),
                const SizedBox(height: 12),

                // Address
                TextFormField(
                  controller: _addressController,
                  decoration: _dec('Address'),
                  minLines: 2,
                  maxLines: 4,
                  textInputAction: TextInputAction.newline,
                ),
                const SizedBox(height: 18),

                SizedBox(
                  width: double.infinity,
                  height: 52,
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : () => _saveProfile(token),
                    style: ElevatedButton.styleFrom(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    ),
                    child: _isLoading
                        ? const SizedBox(
                            width: 22,
                            height: 22,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                          )
                        : const Text(
                            'Save Changes',
                            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                          ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
      backgroundColor: const Color(0xFFF3F4F6),
    );
  }
}
