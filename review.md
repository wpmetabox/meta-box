# Đánh giá PR #1708 — Fix `[rwmb_meta]` shortcode permission bypass

Phạm vi: `inc/shortcode.php`, `CHANGELOG.md`, `readme.txt`, `.github/workflows/opencode-review.yml`, `tests/phpunit/ShortcodePermissionTest.php`.

Đã kiểm tra: `php -l` không lỗi cú pháp. Không chạy được `composer phpcs` trong môi trường này (thiếu binary `phpcs`). Dưới đây chỉ liệt kê các vấn đề còn tồn tại/cần lưu ý.

## Logic

- **`user_can_read_term()` gọi `get_term( $term_id )` thiếu `taxonomy`** (inc/shortcode.php:189). Với term_id dùng chung nhiều taxonomy, `WP_Term::get_instance()` trả `WP_Error( 'ambiguous_term_id' )`, khiến hàm từ chối (inc/shortcode.php:190) dù term công khai — và từ chối cả admin, vì không chạy tới `edit_term`. Cần truyền `taxonomy` (từ `$atts['taxonomy']` nếu có) cho `get_term()`. Hàm hiện không nhận `$atts` nên chưa thể lấy taxonomy.

- **`network_setting` được xử lý giống hệt `setting`** (inc/shortcode.php:145-147 → `user_can_read_setting()`). Hàm này chỉ dựa vào `capability` của settings page, mặc định `edit_theme_options`, và bỏ qua cờ `network`. Trên multisite, `edit_theme_options` là capability cấp site, nên quản trị viên một site (không có `manage_network_options`) có thể đọc meta của option cấp network qua shortcode. Cần xác nhận default capability cho network page của MB Settings Page và ưu tiên `manage_network_options` khi page có `network => true`.

- **`user_can_read_post()` trả `true` cho mọi post `publish` không mật khẩu, bất kể post type có public hay không** (inc/shortcode.php:170). Một bài thuộc post type ẩn/không public (`public => false`) vẫn đọc được ẩn danh qua shortcode. Nên dùng `is_post_publicly_viewable( $post )` (WP ≥ 5.7). Đây là logic cũ được giữ lại nhưng nằm trong phần code mới nên vẫn nên siết.

- **`is_term_publicly_viewable()` kiểm tra `publicly_queryable`, không phải `public`** (WP core). Taxonomy đặt `public => true` nhưng `publicly_queryable => false` sẽ bị coi là riêng tư và cần `edit_term`; cần cân nhắc đây có phải hành vi mong muốn.

- **`user_can_read_model()` (inc/shortcode.php:246-269):**
  - `$model->capability` (inc/shortcode.php:267) sẽ phát sinh cảnh báo "Undefined property" nếu class Model không có thuộc tính này; nên dùng `$model->capability ?? 'edit_posts'`. Không kiểm chứng được vì `\MetaBox\CustomTable\Model\Factory` thuộc extension trả phí, không có trong repo.
  - Nhánh fallback dò `rwmb_get_registry( 'field' )->get_by_object_type( 'model' )` (inc/shortcode.php:253) duyệt `foreach ( $models as $name => $fields )`, nhưng khóa `$name` là **field type**, không phải tên model (xem `RWMB_Field_Registry::add()`), nên `Factory::get( $name )` nhiều khả năng sai. Thực tế tài liệu yêu cầu truyền `type`, nên nhánh fallback này khó dùng và có thể bỏ cho gọn.

## Bảo mật

- Bản vá chặn đúng đường rò rỉ chính (IDOR user/setting/comment/term qua shortcode). Vấn đề còn lại đáng chú ý nhất là `network_setting` nêu ở trên: capability mặc định cấp site có thể cho phép đọc dữ liệu cấp network.

## Tương thích

- **`init(): void` (inc/shortcode.php:12) đổi chữ ký method `public`.** Lớp con/extension ghi đè `init()` không khai báo `: void` sẽ fatal. Rủi ro thấp (không thấy lớp con trong repo) nhưng là thay đổi API công khai, nên cân nhắc bỏ `: void` để giữ tương thích.

- **Test dùng `ReflectionMethod::setAccessible()`** (tests/phpunit/ShortcodePermissionTest.php:27, 145). Từ PHP 8.1 method này là no-op và **bị deprecate từ PHP 8.5**, sẽ sinh deprecation warning. Nếu cần hỗ trợ PHP < 8.1, bọc bằng `if ( PHP_VERSION_ID < 80100 )`.

- **Lệch version**: `CHANGELOG.md:1` và `readme.txt:119` ghi `5.15.3` (không có ngày, khác mục trước đó dạng `5.15.2 - 2026-10-07`), trong khi `meta-box.php:6` vẫn `Version: 5.15.2` và `readme.txt` vẫn `Stable tag: 5.15.2`. Nên thống nhất ngày/version hoặc tách bước bump version.

## Chuẩn viết code

- `user_can_read_object()` có tham số mặc định `string $field_id = ''`, `array $atts = []` (inc/shortcode.php:131) chỉ phục vụ test; trong code sản phẩm luôn truyền đủ 4 tham số. Có thể bỏ mặc định cho rõ ràng (hoặc giữ nếu muốn test gọi ngắn).

- Test: `remove_all_filters( 'rwmb_meta_shortcode_permission' )` nằm sau `assert…` (tests/phpunit/ShortcodePermissionTest.php:52-59); nếu assert fail, filter rò rỉ sang test khác. `wp_set_current_user()` cũng không được khôi phục về user cũ sau test (tests/phpunit/ShortcodePermissionTest.php:96, 155). Nên đặt cleanup trong `tearDown()`.

## Tối ưu

- Logic map capability/vòng đời kiểm tra quyền **trùng và lệch** với `MetaBox\Abilities::map_capability()` (src/Abilities.php:300): Abilities dùng `manage_options` cho `setting`, `assign_term` cho đọc `term`; shortcode dùng `capability` page và `is_term_publicly_viewable || edit_term`. Nên tách một helper dùng chung để tránh hai nơi lệch hành vi khi sửa về sau.

- **Test tự động cho bản vá chưa thực sự chạy trong môi trường mặc định.** Không có workflow nào chạy `phpunit`; bootstrap không có WordPress sẽ dùng stub `tests/phpunit/stubs/wordpress.php`, mà stub không có `current_user_can`, `get_post`, `get_term`, `add_filter`, `wp_set_current_user`… nên 5/7 test mới bị `markTestSkipped` (chỉ 2 test không phụ thuộc WP chạy được). Cần bổ sung stub hoặc CI có WordPress để các test IDOR thực sự chạy.
