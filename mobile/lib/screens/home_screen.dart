// screens/home_screen.dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../providers/appointment_provider.dart';
import '../providers/notification_provider.dart';
import '../widgets/notification_bell.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _selectedIndex = 0;

  // Static oral care tips for patients
  final List<Map<String, String>> _oralCareTips = [
    {
      'title': 'Brush twice a day',
      'description':
          'Brush your teeth for at least 2 minutes using a soft-bristled toothbrush and fluoride toothpaste.',
    },
    {
      'title': 'Do not forget to floss',
      'description':
          'Floss once a day to remove food and plaque between teeth where the toothbrush cannot reach.',
    },
    {
      'title': 'Limit sugary food & drinks',
      'description':
          'Sugary snacks and drinks increase the risk of cavities. Rinse your mouth or drink water after sweets.',
    },
    {
      'title': 'Regular dental check-ups',
      'description':
          'Visit your dentist every 6 months or as advised to keep your teeth and gums healthy.',
    },
    {
      'title': 'Avoid smoking',
      'description':
          'Smoking can cause gum disease, tooth loss, and oral cancer. Avoid tobacco products for a healthier mouth.',
    },
  ];

  @override
  void initState() {
    super.initState();

    Future.microtask(() {
      final auth = context.read<AuthProvider>();
      final notif = context.read<NotificationProvider>();

      if (auth.token != null) {
        notif.startPolling(auth.token!);
      }
    });

    _loadDashboard();
  }

  Future<void> _loadDashboard() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final appointmentProvider = Provider.of<AppointmentProvider>(
      context,
      listen: false,
    );

    if (authProvider.token != null) {
      try {
        await appointmentProvider.loadDashboardData(authProvider.token!);
        await appointmentProvider.loadServices(authProvider.token!);
      } catch (e) {
        // Handle error silently
      }
    }
  }

  void _onBottomNavTap(int index) {
    setState(() => _selectedIndex = index);

    switch (index) {
      case 0:
        break;
      case 1:
        Navigator.pushNamed(context, '/appointments');
        break;
      case 2:
        Navigator.pushNamed(context, '/create-appointment');
        break;
      case 3:
        Navigator.pushNamed(context, '/reports');
        break;
      case 4:
        Navigator.pushNamed(context, '/profile');
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final appointmentProvider = Provider.of<AppointmentProvider>(context);

    return Scaffold(
      body: RefreshIndicator(
        onRefresh: _loadDashboard,
        child: CustomScrollView(
          slivers: [
            // Custom App Bar with Gradient
            SliverToBoxAdapter(
              child: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [Color(0xFF6B5CB8), Color(0xFF8B7DC8)],
                  ),
                  borderRadius: BorderRadius.only(
                    bottomLeft: Radius.circular(30),
                    bottomRight: Radius.circular(30),
                  ),
                ),
                child: SafeArea(
                  bottom: false,
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Top bar with menu and notifications
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            IconButton(
                              icon: const Icon(Icons.menu, color: Colors.white),
                              onPressed: () {
                                _showMenuDialog(context);
                              },
                            ),

                            // ✅ REPLACE old bell IconButton with NotificationBell()
                            const NotificationBell(iconColor: Colors.white),
                          ],
                        ),
                        const SizedBox(height: 16),

                        // Welcome text
                        Text(
                          'Welcome Back, ${authProvider.patient?.firstName ?? 'Patient'}',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Here is an overview of your dental activity.',
                          style: TextStyle(color: Colors.white70, fontSize: 14),
                        ),
                        const SizedBox(height: 20),
                      ],
                    ),
                  ),
                ),
              ),
            ),

            // Quick Stats
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Row(
                  children: [
                    Expanded(
                      child: _buildStatCard(
                        'Appointments',
                        appointmentProvider
                                .dashboardStats?['total_appointments']
                                ?.toString() ??
                            '0',
                        Icons.calendar_today,
                        const Color(0xFF6B5CB8),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: _buildStatCard(
                        'Upcoming',
                        appointmentProvider.dashboardStats?['pending']
                                ?.toString() ??
                            '0',
                        Icons.upcoming,
                        const Color(0xFFFF6B9D),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Next Appointment / Summary section
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: _buildNextAppointmentCard(appointmentProvider),
              ),
            ),

            const SliverToBoxAdapter(child: SizedBox(height: 16)),

            // Oral Care Tips Header
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 8,
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: const [
                    Text(
                      'Oral Care Tips',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    Text(
                      'For healthier teeth',
                      style: TextStyle(fontSize: 12, color: Colors.grey),
                    ),
                  ],
                ),
              ),
            ),

            // Oral Care Tips List
            SliverPadding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate((context, index) {
                  final tip = _oralCareTips[index];
                  return Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.05),
                          blurRadius: 10,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: const Color(0xFF6B5CB8).withOpacity(0.1),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(
                            Icons.health_and_safety,
                            color: Color(0xFF6B5CB8),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                tip['title'] ?? '',
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 16,
                                ),
                              ),
                              const SizedBox(height: 6),
                              Text(
                                tip['description'] ?? '',
                                style: TextStyle(
                                  fontSize: 13,
                                  color: Colors.grey[700],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  );
                }, childCount: _oralCareTips.length),
              ),
            ),

            const SliverToBoxAdapter(child: SizedBox(height: 100)),
          ],
        ),
      ),

      // Modern Bottom Navigation
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: const BorderRadius.only(
            topLeft: Radius.circular(30),
            topRight: Radius.circular(30),
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 20,
              offset: const Offset(0, -5),
            ),
          ],
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                _buildNavItem(Icons.home, 0),
                _buildNavItem(Icons.calendar_today, 1),
                GestureDetector(
                  onTap: () => _onBottomNavTap(2),
                  child: _buildFloatingActionButton(),
                ),
                _buildNavItem(Icons.bar_chart, 3),
                _buildNavItem(Icons.person_outline, 4),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildStatCard(
    String label,
    String value,
    IconData icon,
    Color color,
  ) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color, size: 24),
          ),
          const SizedBox(height: 12),
          Text(
            value,
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          const SizedBox(height: 4),
          Text(label, style: TextStyle(fontSize: 12, color: Colors.grey[600])),
        ],
      ),
    );
  }

  // Next appointment summary card, using upcomingAppointments
  Widget _buildNextAppointmentCard(AppointmentProvider appointmentProvider) {
    if (appointmentProvider.upcomingAppointments.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 10,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFFFC857).withOpacity(0.2),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(Icons.event_busy, color: Color(0xFFFFC857)),
            ),
            const SizedBox(width: 12),
            const Expanded(
              child: Text(
                'You do not have any upcoming appointment yet. Book one to keep your teeth healthy!',
                style: TextStyle(fontSize: 14),
              ),
            ),
            TextButton(
              onPressed: () {
                Navigator.pushNamed(context, '/create-appointment');
              },
              child: const Text('Book'),
            ),
          ],
        ),
      );
    }

    final nextAppointment = appointmentProvider.upcomingAppointments.first;
    final String dateText = nextAppointment.scheduledDate;

    String serviceName = 'Dental Service';
    if (nextAppointment.servicesList.isNotEmpty) {
      if (nextAppointment.servicesList.length == 1) {
        serviceName = nextAppointment.servicesList.first;
      } else {
        serviceName =
            '${nextAppointment.servicesList.first} + ${nextAppointment.servicesList.length - 1} more';
      }
    }

    final String status = nextAppointment.status;

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF6B5CB8), Color(0xFF8B7DC8)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF6B5CB8).withOpacity(0.3),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          const Icon(Icons.event_available, color: Colors.white, size: 32),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Next Appointment',
                  style: TextStyle(color: Colors.white70, fontSize: 12),
                ),
                const SizedBox(height: 4),
                Text(
                  serviceName,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  dateText,
                  style: const TextStyle(color: Colors.white70, fontSize: 13),
                ),
                if (nextAppointment.queueNumber != null) ...[
                  const SizedBox(height: 4),
                  Text(
                    'Queue Number: ${nextAppointment.queueNumber}',
                    style: const TextStyle(color: Colors.white70, fontSize: 12),
                  ),
                ],
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(
              status,
              style: const TextStyle(color: Colors.white, fontSize: 12),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildNavItem(IconData icon, int index) {
    final isSelected = _selectedIndex == index;
    return GestureDetector(
      onTap: () => _onBottomNavTap(index),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isSelected
              ? const Color(0xFF6B5CB8).withOpacity(0.1)
              : Colors.transparent,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Icon(
          icon,
          color: isSelected ? const Color(0xFF6B5CB8) : Colors.grey,
          size: 28,
        ),
      ),
    );
  }

  Widget _buildFloatingActionButton() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [Color(0xFF6B5CB8), Color(0xFF8B7DC8)],
        ),
        shape: BoxShape.circle,
        boxShadow: [
          BoxShadow(
            color: Color(0xFF6B5CB8),
            blurRadius: 15,
            offset: Offset(0, 5),
          ),
        ],
      ),
      child: const Icon(Icons.add, color: Colors.white, size: 32),
    );
  }

  void _showMenuDialog(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Menu'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.person),
              title: const Text('Profile'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/profile');
              },
            ),
            ListTile(
              leading: const Icon(Icons.calendar_today),
              title: const Text('My Appointments'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/appointments');
              },
            ),
            ListTile(
              leading: const Icon(Icons.bar_chart),
              title: const Text('Reports'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/reports');
              },
            ),
            const Divider(),
            ListTile(
              leading: const Icon(Icons.logout, color: Colors.red),
              title: const Text('Logout', style: TextStyle(color: Colors.red)),
              onTap: () async {
                Navigator.pop(context);
                await authProvider.logout();
                if (context.mounted) {
                  Navigator.pushNamedAndRemoveUntil(
                    context,
                    '/login',
                    (route) => false,
                  );
                }
              },
            ),
          ],
        ),
      ),
    );
  }
}
