import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/appointment_provider.dart';

class ReportsScreen extends StatefulWidget {
  const ReportsScreen({super.key});

  @override
  State<ReportsScreen> createState() => _ReportsScreenState();
}

class _ReportsScreenState extends State<ReportsScreen> {
  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final appointmentProvider =
        Provider.of<AppointmentProvider>(context, listen: false);

    if (authProvider.token != null) {
      try {
        await appointmentProvider.loadDashboardData(authProvider.token!);
        await appointmentProvider.loadAppointments(authProvider.token!);
      } catch (e) {}
    }
  }

  // Export Method
  Future<void> _exportReports() async {
  final authProvider = Provider.of<AuthProvider>(context, listen: false);
  final appointmentProvider =
      Provider.of<AppointmentProvider>(context, listen: false);

  if (authProvider.token == null) return;

  ScaffoldMessenger.of(context).showSnackBar(
    const SnackBar(content: Text("Exporting report... please wait")),
  );

  try {
    // Call the export function in the provider
    await appointmentProvider.exportReports(authProvider.token!);

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text("Report exported successfully")),
    );
  } catch (e) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text("Failed to export report")),
    );
  }
}


  @override
  Widget build(BuildContext context) {
    final appointmentProvider = Provider.of<AppointmentProvider>(context);
    final stats = appointmentProvider.dashboardStats;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Reports & Statistics'),
        backgroundColor: Colors.deepPurpleAccent,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.download),
            tooltip: "Export CSV",
            onPressed: _exportReports,  // Trigger export when pressed
          )
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: appointmentProvider.isLoading
            ? const Center(child: CircularProgressIndicator())
            : SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Appointment Statistics',
                      style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Display the stats cards if data is available
                    if (stats != null) ...[ 
                      Card(
                        elevation: 4,
                        child: Padding(
                          padding: const EdgeInsets.all(20),
                          child: Column(
                            children: [
                              const Text(
                                'Total Appointments',
                                style: TextStyle(
                                  fontSize: 16,
                                  color: Colors.grey,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                stats['total_appointments'].toString(),
                                style: const TextStyle(
                                  fontSize: 48,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.blue,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                      const Text(
                        'Status Breakdown',
                        style: TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 16),

                      _buildStatusCard(
                        'Pending',
                        stats['pending'],
                        stats['total_appointments'],
                        Colors.orange,
                        Icons.hourglass_empty,
                      ),
                      const SizedBox(height: 12),

                      _buildStatusCard(
                        'Confirmed',
                        stats['confirmed'],
                        stats['total_appointments'],
                        Colors.blue,
                        Icons.check_circle,
                      ),
                      const SizedBox(height: 12),

                      _buildStatusCard(
                        'Completed',
                        stats['completed'],
                        stats['total_appointments'],
                        Colors.green,
                        Icons.done_all,
                      ),
                      const SizedBox(height: 12),

                      _buildStatusCard(
                        'Canceled',
                        stats['canceled'],
                        stats['total_appointments'],
                        Colors.red,
                        Icons.cancel,
                      ),
                    ],
                    
                    const SizedBox(height: 32),

                    const Text(
                      'Recent Activity',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 16),

                    if (appointmentProvider.appointments.isEmpty)
                      const Card(
                        child: Padding(
                          padding: EdgeInsets.all(24),
                          child: Center(child: Text('No activity yet')),
                        ),
                      )
                    else
                      ...appointmentProvider.appointments.take(5).map((apt) {
                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          child: ListTile(
                            leading: CircleAvatar(
                              backgroundColor: _getStatusColor(apt.status),
                              child: Text(
                                'Q${apt.queueNumber}',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                            title: Text('Appointment #${apt.appointmentId}'),
                            subtitle: Text(apt.scheduledDate),
                            trailing: Chip(
                              label: Text(
                                apt.status,
                                style: const TextStyle(fontSize: 12),
                              ),
                              backgroundColor: _getStatusColor(
                                apt.status,
                              ).withOpacity(0.2),
                            ),
                          ),
                        );
                      }),
                  ],
                ),
              ),
      ),
    );
  }

  // Helper method for status card display
  Widget _buildStatusCard(
    String title,
    int count,
    int total,
    Color color,
    IconData icon,
  ) {
    double percentage = total > 0 ? (count / total * 100) : 0;

    return Card(
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: color, size: 32),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '$count appointments (${percentage.toStringAsFixed(1)}%)',
                    style: TextStyle(fontSize: 14, color: Colors.grey[600]),
                  ),
                ],
              ),
            ),
            Text(
              count.toString(),
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // Method to determine the color based on the status
  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return Colors.orange;
      case 'confirmed':
        return Colors.blue;
      case 'completed':
        return Colors.green;
      case 'canceled':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}
