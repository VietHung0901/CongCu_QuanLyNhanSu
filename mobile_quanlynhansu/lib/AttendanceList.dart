import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';
import 'package:mobile_quanlynhansu/Class/StringURL.dart';

class AttendanceScreen extends StatefulWidget {
  final String username;

  const AttendanceScreen({Key? key, required this.username}) : super(key: key);

  @override
  _AttendanceScreenState createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  String baseUrl = StringURL().baseUrl;
  List<dynamic> attendanceData = [];
  bool isLoading = false;
  String errorMessage = '';

  int selectedMonth = DateTime.now().month;
  int selectedYear = DateTime.now().year;

  @override
  void initState() {
    super.initState();
    fetchAttendance(); // Gọi API ngay khi vào màn hình
  }

  Future<void> fetchAttendance() async {
    setState(() {
      isLoading = true;
      errorMessage = '';
    });

    final url = Uri.parse(
        '$baseUrl/AttendaceDetailController.php?username=${widget.username}&month=$selectedMonth&year=$selectedYear');

    try {
      final response = await http.get(url);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        setState(() {
          attendanceData = data['data'];
          isLoading = false;
        });
      } else {
        setState(() {
          errorMessage = "Lỗi: ${response.body}";
          isLoading = false;
        });
      }
    } catch (error) {
      setState(() {
        errorMessage = "Lỗi kết nối: $error";
        isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Chấm công của ${widget.username}')),
      body: Column(
        children: [
          // Chọn tháng & năm
          Padding(
            padding: const EdgeInsets.all(10.0),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // Chọn tháng
                DropdownButton<int>(
                  value: selectedMonth,
                  items: List.generate(12, (index) => index + 1)
                      .map((month) => DropdownMenuItem(
                            value: month,
                            child: Text("Tháng $month"),
                          ))
                      .toList(),
                  onChanged: (value) {
                    setState(() {
                      selectedMonth = value!;
                    });
                  },
                ),
                SizedBox(width: 20),
                // Chọn năm
                DropdownButton<int>(
                  value: selectedYear,
                  items:
                      List.generate(5, (index) => DateTime.now().year - index)
                          .map((year) => DropdownMenuItem(
                                value: year,
                                child: Text("$year"),
                              ))
                          .toList(),
                  onChanged: (value) {
                    setState(() {
                      selectedYear = value!;
                    });
                  },
                ),
                SizedBox(width: 20),
                // Nút xem bảng công
                ElevatedButton(
                  onPressed: fetchAttendance,
                  child: Text("Xem bảng công"),
                )
              ],
            ),
          ),

          // Hiển thị danh sách chấm công
          Expanded(
            child: isLoading
                ? Center(child: CircularProgressIndicator())
                : errorMessage.isNotEmpty
                    ? Center(
                        child: Text(errorMessage,
                            style: TextStyle(color: Colors.red)))
                    : attendanceData.isEmpty
                        ? Center(child: Text("Không có dữ liệu chấm công"))
                        : ListView.builder(
                            itemCount: attendanceData.length,
                            itemBuilder: (context, index) {
                              final record = attendanceData[index];

                              return Card(
                                margin: EdgeInsets.symmetric(
                                    horizontal: 10, vertical: 5),
                                child: ListTile(
                                  leading: Icon(Icons.account_circle,
                                      color: Colors.blue, size: 40),
                                  subtitle: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                          "Ngày: ${record['check_in_time'].substring(0, 10)}"),
                                      Text(
                                          "Giờ vào: ${record['check_in_time'].substring(11)} (${record['check_type_in']})"),
                                      Text(
                                        "Giờ ra: ${record['check_out_time']}",
                                        style: TextStyle(
                                            color: record['check_out_time'] ==
                                                    'Chưa check-out'
                                                ? Colors.red
                                                : Colors.black),
                                      ),
                                      Text(
                                        "Loại check-out: ${record['check_type_out']}",
                                        style: TextStyle(
                                            color: record['check_type_out'] ==
                                                    'Chưa có dữ liệu'
                                                ? Colors.red
                                                : Colors.black),
                                      ),
                                      Text("Trạng thái: ${record['status']}"),
                                    ],
                                  ),
                                ),
                              );
                            },
                          ),
          ),
        ],
      ),
    );
  }
}
