import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:mobile_quanlynhansu/Class/LoginRequest.dart';
import 'package:mobile_quanlynhansu/Class/LoginResponse.dart';
import 'package:mobile_quanlynhansu/Class/StringURL.dart';
import 'package:mobile_quanlynhansu/Login.dart';

class AuthService {
  final String baseUrl;

  AuthService() : baseUrl = StringURL().baseUrl;

  Future<LoginResponse> login(LoginRequest request) async {
    final url = Uri.parse('$baseUrl/UsersController.php?action=login');

    final response = await http.post(
      url,
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode(request.toJson()),
    );

    if (response.statusCode == 200) {
      // Parse JSON khi đăng nhập thành công
      final json = jsonDecode(response.body);
      return LoginResponse.fromJson(json);
    } else {
      // Xử lý lỗi
      final error = jsonDecode(response.body);
      throw Exception(error['message']);
    }
  }

  Future<void> logout(BuildContext context) async {
    // Điều hướng về màn hình đăng nhập và xóa toàn bộ lịch sử điều hướng
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (context) => LoginScreen()),
      (Route<dynamic> route) => false, // Điều kiện xóa toàn bộ route
    );
  }
}
