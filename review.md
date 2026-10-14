# Đánh giá PR #1708 — Fix `[rwmb_meta]` shortcode permission bypass

Phạm vi: chỉ 1 file thay đổi `inc/shortcode.php`.

## Logic

- `user_can_read_setting()` (inc/shortcode.php:197-208) chỉ so khớp theo khóa `option_name`. Theo tài liệu MB Settings Page, `option_name` là **tùy chọn** và mặc định lấy bằng `id` khi không khai báo. Vì vậy các settings page chỉ khai báo `id` (không khai báo `option_name`) sẽ luôn bị từ chối, khiến shortcode `object_type="setting"` hợp lệ bị ẩn giá trị. Nên so khớp cả trường hợp mặc định, ví dụ: `$option_name === ( $page['option_name'] ?? $page['id'] ?? '' )`.
- Trong cùng hàm, `$page['capability'] ?? 'edit_theme_options'` (inc/shortcode.php:204) chỉ xử lý được trường hợp thiếu khóa, không xử lý được trường hợp giá trị rỗng (`''`) — khi đó `current_user_can( '' )` trả về `false`. Nên dùng `?:` để bao phủ cả giá trị rỗng.
- `user` (inc/shortcode.php:119-120) dùng `edit_user`. Điều này chặn cả người dùng ẩn danh đọc meta công khai của user (ví dụ bio/tác giả hiển thị ngoài frontend) — một thay đổi hành vi có thể làm hỏng các shortcode đang chạy. User đã đăng nhập vẫn tự đọc được do `map_meta_cap('edit_user', $self)` cho phép, nhưng khách thì không. Cần cân nhắc hoặc ghi rõ trong changelog/tài liệu vì đây là thay đổi phá vỡ tương thích ngược.
- Nhánh `default => false` (inc/shortcode.php:125-126) là thay đổi phá vỡ tương thích ngược với mọi `object_type` tùy biến do bên thứ ba đăng ký: trước đây code cũ trả về `true` cho mọi loại không phải `post`, giờ bị chặn hoàn toàn. Filter `rwmb_meta_shortcode_permission` là lối thoát, nhưng cần được ghi rõ để nhà phát triển biết.
- `get_value()` trả về `null` khi bị từ chối (inc/shortcode.php:58). Sau đó `register_shortcode()` chạy `do_shortcode( $value )` (inc/shortcode.php:35) — mặc định `render_shortcodes = 'true'` — nên `do_shortcode()` gọi `strpos( null, '[' )`, gây cảnh báo deprecation "Passing null to parameter" trên PHP 8.1+ (tương tự với `wp_kses_post( null )`). Nên trả về chuỗi rỗng `''` thay vì `null`.
- `$object_type = $atts['object_type'] ?? 'post'` (inc/shortcode.php:94): nếu shortcode ghi `object_type=""`, giá trị rỗng không rơi vào `?? 'post'` mà đi thẳng vào nhánh `default` và bị từ chối. Cân nhắc dùng `?:` (hoặc kiểm tra rỗng) để rơi về `post`.
- Việc kiểm tra quyền vẫn chỉ chạy khi `$object_id` khác `null` (inc/shortcode.php:57). Với các `object_type` phi post mà không truyền `object_id`, nhánh kiểm tra bị bỏ qua. Nhiều khả năng `object_id = null` chỉ trỏ tới ngữ cảnh hiện tại (current user/option) nên không khai thác được, nhưng nên xác nhận và ghi chú lại để tránh bỏ sót.

## Chuẩn viết code

- DocBlock của `register_shortcode()` ghi `@return string|mixed` (inc/shortcode.php:13) là dư thừa vì `mixed` đã bao hàm `string`. Nên rút gọn thành `@return mixed`.
- Logic map capability cho `term`/`user`/`comment`/`setting` trùng lặp một phần với `MetaBox\Abilities::map_capability()` (src/Abilities.php:300). Cân nhắc tách thành helper dùng chung để tránh lệch hành vi giữa shortcode và REST/Abilities khi có thay đổi về sau.
