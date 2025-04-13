import 'package:flutter/material.dart';
import 'package:mobile_quanlynhansu/ApiService/Auth/AuthService.dart';
import 'package:mobile_quanlynhansu/Class/LoginRequest.dart';
import 'package:mobile_quanlynhansu/home.dart';

class LoginScreen extends StatefulWidget {
  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  bool _isLoading = false;
  final TextEditingController usernameController = TextEditingController();
  final TextEditingController passwordController = TextEditingController();
  final AuthService authService = AuthService();

  void handleLogin() async {
    if (usernameController.text.isEmpty || passwordController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
            content: Text('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!')),
      );
      return;
    }
    try {
      final request = LoginRequest(
        username: usernameController.text,
        password: passwordController.text,
      );

      setState(() {
        _isLoading = true;
      });

      final response = await authService.login(request);

      setState(() {
        _isLoading = false;
      });

      if (response.status == "success") {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
              builder: (context) => HomeScreen(username: response.username)),
        );
      } else {
        // Hiển thị lỗi từ API (ví dụ: Sai tài khoản/mật khẩu)
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response.message ),
          ),
        );
      }
    } catch (e) {
      // Reset trạng thái _isLoading nếu có lỗi
      setState(() {
        _isLoading = false;
      });

      // Hiển thị lỗi
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text('Đăng nhập thất bại! Lỗi: $e'),
      ));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("HỆ THỐNG CHẤM CÔNG"),
        backgroundColor: Colors.blueAccent,
      ),
      body: OrientationBuilder(
        builder: (context, orientation) {
          return SingleChildScrollView(
            child: Padding(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: orientation == Orientation.portrait
                    ? CrossAxisAlignment.center
                    : CrossAxisAlignment.start,
                children: [
                  const Center(
                    child: Icon(
                      Icons.business_center, // Biểu tượng công việc
                      size: 100,
                      color: Colors.blueAccent,
                    ),
                  ),
                  const SizedBox(height: 20),
                  const Text(
                    'XIN CHÀO',
                    style: TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: Colors.blueAccent,
                    ),
                  ),
                  const SizedBox(height: 10),
                  Text(
                    'Đăng nhập để chấm công',
                    style: TextStyle(
                      fontSize: 16,
                      color: Colors.grey[600],
                    ),
                  ),
                  const SizedBox(height: 30),
                  TextField(
                    controller: usernameController,
                    decoration: const InputDecoration(
                      labelText: "Tên đăng nhập",
                      prefixIcon: Icon(Icons.person),
                      border: OutlineInputBorder(),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: passwordController,
                    obscureText: true,
                    decoration: const InputDecoration(
                      labelText: "Mật khẩu",
                      prefixIcon: Icon(Icons.lock),
                      border: OutlineInputBorder(),
                    ),
                  ),
                  const SizedBox(height: 24),
                  _isLoading
                      ? const CircularProgressIndicator()
                      : ElevatedButton(
                          onPressed: handleLogin,
                          child: const Text("Đăng nhập"),
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 100, vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                            backgroundColor: Colors.blueAccent,
                          ),
                        ),
                  const SizedBox(height: 20),
                  TextButton(
                    onPressed: () {
                      Navigator.pushNamed(context, '/forgot-password');
                    },
                    child: const Text("Quên mật khẩu"),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
