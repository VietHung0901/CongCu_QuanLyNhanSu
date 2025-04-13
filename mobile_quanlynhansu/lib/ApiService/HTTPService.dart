import 'package:http/http.dart' as http;
import 'package:mobile_quanlynhansu/Class/StringURL.dart';

class HTTPService {
  final String baseUrl;

  HTTPService() : baseUrl = StringURL().baseUrl;

  Future<http.Response> get(String endpoint) async {
    final url = Uri.parse('$baseUrl$endpoint');
    return http.get(url);
  }

  Future<http.Response> post(String endpoint, Map<String, dynamic> body) async {
    final url = Uri.parse('$baseUrl$endpoint');
    return http.post(url);
  }

  Future<http.Response> put(String endpoint, Map<String, dynamic> body) async {
    final url = Uri.parse('$baseUrl$endpoint');
    return http.put(url);
  }
}
