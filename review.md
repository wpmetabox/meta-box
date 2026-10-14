# Đánh giá PR #1708 — Fix `[rwmb_meta]` shortcode permission bypass

Phạm vi thay đổi: `inc/shortcode.php`, `CHANGELOG.md`, `readme.txt`.

Kiểm tra tự động: `php -l` không có lỗi cú pháp; `phpcs --standard=phpcs.xml inc/shortcode.php` không báo lỗi. Dưới đây chỉ liệt kê các vấn đề còn tồn tại.

## Logic / Tương thích

- **`network_setting` bị chặn oan.** `user_can_read_object()` (inc/shortcode.php:108) chỉ xử lý `post`, `term`, `comment`, `user`, `setting`; mọi giá trị khác rơi vào `default => false` (inc/shortcode.php:125). Meta Box chính thức hỗ trợ `object_type => 'network_setting'` cho network settings (xem tài liệu MB Settings Page), và nó cũng đăng ký qua filter `mb_settings_pages` với `'network' => true`. Vì vậy `[rwmb_meta object_type="network_setting" ...]` sẽ bị ẩn giá trị ngay cả với admin. Changelog chỉ ghi chung "unknown or custom object_type" nên người dùng không lường được đây là object type có sẵn. Cần thêm case `network_setting` (kiểm tra capability của settings page tương ứng, ví dụ `manage_network_options` hoặc `capability` của page).

- **`user` dùng `edit_user` làm chặn việc hiển thị user meta công khai.** inc/shortcode.php:119-120. Khách ẩn danh không còn đọc được user meta qua shortcode (ví dụ bio/social link của tác giả ngoài frontend). User đã đăng nhập vẫn tự đọc được do `map_meta_cap` ánh xạ `edit_user` của chính mình. Đây đã được ghi "Breaking" trong changelog, nhưng nên cân nhắc lại: WordPress không có capability "read_user" nên nếu muốn cho phép đọc công khai có kiểm soát, cần thêm filter/tham số thay vì khóa cứng.

- **`default => false` phá vỡ tương thích ngược** với mọi `object_type` do bên thứ ba đăng ký qua `rwmb_get_storage` (inc/shortcode.php:125-126). Đã có filter thoát `rwmb_meta_shortcode_permission`, nhưng filter nhận `$atts` đã bị `unset( $atts['id'] )` (inc/shortcode.php:32) nên không lấy được field id để quyết định theo từng field. Nên truyền thêm `$field_id` (hoặc giữ `id` trong `$atts`) cho filter.

- **`user_can_read_term()` gọi `get_term( $term_id )` thiếu taxonomy** (inc/shortcode.php:162). Với term_id dùng chung giữa nhiều taxonomy, `WP_Term::get_instance()` trả `WP_Error( 'ambiguous_term_id' )`, khiến code từ chối (inc/shortcode.php:163) dù term hoàn toàn công khai. Nên truyền `taxonomy` (nếu shortcode có atts `taxonomy`) cho `get_term()`. Đây là trường hợp biên nhưng dễ tránh.

- **Kiểm tra quyền chỉ chạy khi `object_id` khác `null`** (inc/shortcode.php:57). Đã được ghi chú trong changelog. Về logic hiện tại không thấy lỗ hổng (khi thiếu `object_id`, storage dùng ngữ cảnh hiện tại), nhưng nên xác nhận cụ thể với `object_type="user"`/`"term"` không truyền `object_id` để chắc chắn không trỏ tới đối tượng ngoài ngữ cảnh.

## Chuẩn viết code

- `init(): void` (inc/shortcode.php:5) là method `public`. Nếu có lớp con/extension ghi đè `init()` mà không khai báo `: void`, PHP sẽ fatal. Rủi ro thấp (không thấy lớp con trong repo) nhưng nên lưu ý vì đây là thay đổi chữ ký method public.

## Tối ưu

- **Trùng lặp logic map capability** với `MetaBox\Abilities::map_capability()` (src/Abilities.php:300) nhưng lại khác nhau: Abilities dùng `manage_options` cho `setting`/`assign_term` cho `term` khi read, còn shortcode dùng `capability` của settings page và `is_term_publicly_viewable || edit_term`. Nên thống nhất hoặc tách helper chung để tránh lệch hành vi khi một bên được sửa.

- **`user_can_read_setting()` gọi `apply_filters( 'mb_settings_pages', [] )` mỗi lần render shortcode** (inc/shortcode.php:198). Đây là filter do extension MB Settings Page định nghĩa; gọi lặp lại có thể tốn kém và tạo phụ thuộc cứng vào extension. Cân nhắc lấy danh sách settings page một lần hoặc chỉ chạy kiểm tra khi thực sự cần.

## Khác

- PR chưa có test tự động cho lỗ hổng vừa vá (test plan toàn bộ là thủ công). Với một bản vá bảo mật/IDOR nên bổ sung test PHPUnit để tránh hồi quy.
- Tiêu đề phiên bản mới `5.15.3` trong `CHANGELOG.md`/`readme.txt` chưa có ngày, lệch với các mục trước đó (ví dụ `5.15.2 - 2026-10-07`). Nếu chưa phải lúc release thì có thể bỏ qua, nhưng nên thống nhất.
