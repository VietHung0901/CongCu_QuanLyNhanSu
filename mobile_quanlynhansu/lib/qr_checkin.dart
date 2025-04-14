import 'package:flutter/material.dart';
import 'package:mobile_quanlynhansu/Class/StringURL.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:geolocator/geolocator.dart';
import 'package:mobile_quanlynhansu/CheckInSuccessScreen.dart';

class QRCheckInScreen extends StatefulWidget {
  final String username;

  const QRCheckInScreen({Key? key, required this.username}) : super(key: key);

  @override
  _QRCheckInScreenState createState() => _QRCheckInScreenState();
}

class _QRCheckInScreenState extends State<QRCheckInScreen> {
  bool isProcessing = false; // Tránh quét nhiều lần
  String baseUrl = StringURL().baseUrl;
  bool daquet = false;

  Future<void> checkIn(String qrData) async {
    setState(() {
      isProcessing = true;
    });
    Position position = await _determinePosition();

    final url = Uri.parse('$baseUrl/AttendanceController.php');

    try {
      final response = await http.post(url, body: {
        "employee_code": widget.username,
        "qr_code": qrData,
        "latitude": position.latitude.toString(),
        "longitude": position.longitude.toString(),
      });

      final data = jsonDecode(response.body);

      if (data["status"] == "success") {
        setState(() {
          daquet = true;
        });

        // Chuyển hướng đến màn hình điểm danh thành công
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => CheckInSuccessScreen(
              username: widget.username,
              message: data["message"],
              checkInTime: data['check_in_time'],
            ),
          ),
        );
      } else {
        _showDialog("❌ Lỗi!", data["message"]);
      }
    } catch (e) {
      _showDialog("❌ Lỗi kết nối", "Lỗi: ${e.toString()}");
    } finally {
      setState(() {
        isProcessing = false;
      });
    }
  }

  Future<Position> _determinePosition() async {
    bool serviceEnabled;
    LocationPermission permission;

    serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      return Future.error("Dịch vụ vị trí chưa được bật.");
    }

    permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        return Future.error("Quyền truy cập vị trí bị từ chối.");
      }
    }

    if (permission == LocationPermission.deniedForever) {
      return Future.error("Quyền truy cập vị trí bị chặn vĩnh viễn.");
    }

    return await Geolocator.getCurrentPosition();
  }

  void _showDialog(String title, String message) {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text(title),
          content: Text(message),
          actions: [
            TextButton(
              child: Text("OK"),
              onPressed: () => Navigator.of(context).pop(),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text("Quét QR Code")),
      body: MobileScanner(
        controller: MobileScannerController(
          detectionSpeed:
              DetectionSpeed.noDuplicates, // Ngăn chặn quét nhiều lần liên tục
        ),
        onDetect: (capture) {
          final List<Barcode> barcodes = capture.barcodes;
          if (barcodes.isNotEmpty && !isProcessing) {
            String scannedData = barcodes.first.rawValue!;
            if (!daquet) {
              checkIn(scannedData);
            }
          }
        },
      ),
    );
  }
}
