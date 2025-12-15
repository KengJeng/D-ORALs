import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../providers/auth_provider.dart';
import '../providers/notification_provider.dart';

class NotificationBell extends StatelessWidget {
  final Color iconColor;

  const NotificationBell({super.key, this.iconColor = Colors.black});

  @override
  Widget build(BuildContext context) {
    return Consumer<NotificationProvider>(
      builder: (context, provider, _) {
        return Stack(
          children: [
            IconButton(
              icon: Icon(Icons.notifications_outlined, color: iconColor),
              onPressed: () => _showNotifications(context),
            ),
            if (provider.unreadCount > 0)
              Positioned(
                right: 6,
                top: 6,
                child: Container(
                  padding: const EdgeInsets.all(4),
                  decoration: const BoxDecoration(
                    color: Colors.red,
                    shape: BoxShape.circle,
                  ),
                  constraints: const BoxConstraints(
                    minWidth: 18,
                    minHeight: 18,
                  ),
                  child: Text(
                    provider.unreadCount.toString(),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
              ),
          ],
        );
      },
    );
  }

  void _showNotifications(BuildContext context) {
    final auth = context.read<AuthProvider>();
    final token = auth.token; // String? in your app

    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (sheetContext) {
        return Consumer<NotificationProvider>(
          builder: (context, provider, _) {
            if (provider.notifications.isEmpty) {
              return const Padding(
                padding: EdgeInsets.all(24),
                child: Center(child: Text('No notifications yet')),
              );
            }

            return ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: provider.notifications.length,
              separatorBuilder: (_, __) => const Divider(),
              itemBuilder: (context, index) {
                final notif = provider.notifications[index];

                return ListTile(
                  leading: Icon(
                    notif.isSent
                        ? Icons.notifications_none
                        : Icons.notifications,
                    color: notif.isSent ? Colors.grey : Colors.blue,
                  ),
                  title: Text(notif.message),
                  subtitle: Text(notif.createdAtText),
                  onTap: () async {
                    // mark as read requires a non-null token
                    if (token != null) {
                      await provider.markAsRead(notif.notificationId);

                    }
                    if (Navigator.of(sheetContext).canPop()) {
                      Navigator.pop(sheetContext);
                    }
                  },
                );
              },
            );
          },
        );
      },
    );
  }
}
