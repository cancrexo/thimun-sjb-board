# SJB Board

Sistema de note passing para THIMUN The Hague. Bandeja, redacción, respuestas y moderación de notas entre delegados, student officers y staff.

## Pantallas

Cada pantalla es una página de WordPress con este slug y este shortcode.

| Página | Shortcode | Uso |
| --- | --- | --- |
| `sjb-message-board` | `[sjb-board]` | Bandeja del usuario |
| `sjb-message-compose` | `[sjb-board-compose]` | Redactar una nota |
| `sjb-message-thread` | `[sjb-board-thread]` | Leer y responder (`?message_id=`) |
| `sjb-message-moderation` | `[sjb-board-moderations]` | Aprobar o rechazar |

La moderación la ven `administrative_staff` y `executive_administrative`.

## Libreta

- **Delegate:** delegates y student officers de su comité.
- **Student officer:** otros student officers, approval panel y delegates de su comité.
- **Resto de roles:** todos los usuarios.

Si la moderación está activa, las notas de delegates y student officers quedan pendientes hasta que se aprueban. Con la moderación apagada salen publicadas.

## Requisitos

- WordPress
- Advanced Custom Fields, campo `committee` en el usuario
- WP User Avatar (`get_wp_user_avatar_src`)
- jQuery y Font Awesome, ya cargados por el tema

El aviso de notas sin leer sale si el tema ejecuta `do_action('show_new_messages_warning')`.

## Datos

El plugin no crea las tablas al activarse. Tienen que existir:

- `wp_sjb_board_messages`
- `wp_sjb_board_participants`

La opción de WordPress es `sjb_board_options` (moderación, debug, scripts en el footer, borrar datos al desinstalar). El ajuste está en **Ajustes → SJB Board**.

Los logs diarios se escriben en la raíz del plugin: `debug_YYYY-MM-DD.log`.
