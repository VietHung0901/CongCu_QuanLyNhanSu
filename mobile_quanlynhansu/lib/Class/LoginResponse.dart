class LoginResponse {
  final String status;
  final String message;
  final String username;
  final String role;
  final int user_id;

  LoginResponse({
    required this.status,
    required this.message,
    required this.username,
    required this.role,
    required this.user_id,

  });

  factory LoginResponse.fromJson(Map<String, dynamic> json) {
  return LoginResponse(
    status: json['status'],
    message: json['message'],
    username: json['data']?['username'],
    role: json['data']?['role'],
    user_id: json['data']?['user_id'],
  );
}

}
