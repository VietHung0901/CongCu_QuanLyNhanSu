import 'package:flutter/material.dart';
import 'package:mobile_quanlynhansu/Class/StringURL.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:lottie/lottie.dart'; // Thêm thư viện animation

class CheckInSuccessScreen extends StatelessWidget {
  final String username;
  final String message;
  final String checkInTime;

  const CheckInSuccessScreen({
    required this.username,
    required this.message,
    required this.checkInTime,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.lightBlue[50],
      appBar: AppBar(
        title: const Text("Điểm danh thành công"),
        backgroundColor: Colors.blueAccent,
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Animation sôi nổi
              Lottie.network(
                'https://lottie.host/59c20be1-94c1-4c06-89b2-1e8aedec4cf5/iMDHSKFovj.json',
                width: 250,
                repeat: false,
              ),

              const SizedBox(height: 20),

              Text(
                "🎉 $message 🎉",
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 26,
                  fontWeight: FontWeight.bold,
                  color: Colors.green,
                ),
              ),

              const SizedBox(height: 20),

              Text(
                "👤 Xin chào, $username",
                style: const TextStyle(fontSize: 20),
              ),

              const SizedBox(height: 10),

              Text(
                "⏰ Thời gian điểm danh: $checkInTime",
                style: const TextStyle(fontSize: 18, color: Colors.black87),
              ),

              const SizedBox(height: 40),

              ElevatedButton.icon(
                onPressed: () {
                  Navigator.pop(context);
                },
                icon: const Icon(Icons.arrow_back),
                label: const Text("Quay lại"),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  padding:
                      const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                  textStyle: const TextStyle(
                      fontSize: 18, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
