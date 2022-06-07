<?php

require_once __DIR__ . '/../bootstrap.php';

Flight::route(
    '/',
    function () {
        echo twig()->load('index.twig')->render();
    }
);

Flight::route(
    'POST /start',
    function () {
        $data = json_decode(file_get_contents(DATABASE_FILE));

        $id = uniqid();

        $chat = new stdClass();
        $chat->user_1 = $_POST['username'];
        $chat->user_2 = null;
        $chat->messages = [];
        $chat->started = time();
        $chat->last_activity = time();

        $data->chats->{$id} = $chat;

        file_put_contents(DATABASE_FILE, json_encode($data));

        setcookie("zuri_chat_data", json_encode(["chat_id" => 1, "username" => $chat->user_1]), time() + (86400 * 30), "/");
        Flight::redirect('/chat/' . $id);
    }
);

Flight::route(
    'POST /join',
    function () {
        $id = $_POST['chat_id'];
        $data = json_decode(file_get_contents(DATABASE_FILE));

        if (!empty($data->chats->{$id})) {
            if ($data->chats->{$id}->last_activity < strtotime("-30 minutes")) {
                unset($data->chats->{$id});
                file_put_contents(DATABASE_FILE, json_encode($data));
                unset($_COOKIE['zuri_chat_data']);
                setcookie('zuri_chat_data', null, -1, '/');
                Flight::redirect('/');
            }
        } else {
            Flight::redirect('/');
        }

        if (isset($_COOKIE['zuri_chat_data'])) {
            Flight::redirect('/chat/' . $id);
        } else {
            $chat = $data->chats->{$id};
            $chat->user_2 = $_POST['join_username'];
            $chat->last_activity = time();
            $data->chats->{$id} = $chat;

            file_put_contents(DATABASE_FILE, json_encode($data));

            setcookie("zuri_chat_data", json_encode(["chat_id" => $id, "username" => $chat->user_2]), time() + (86400 * 30), "/");
            Flight::redirect('/chat/' . $id);
        }
    }
);

Flight::route(
    '/chat/@id',
    function ($id) {
        $data = json_decode(file_get_contents(DATABASE_FILE));
        $chat = @$data->chats->{$id};
        if (!$chat) {
            Flight::redirect('/');
        }

        // echo 'hello world!';
        echo twig()->load('chat.twig')->render(
            [
                'chat' => $chat,
                'id' => $id,
                'cookie' => json_decode($_COOKIE['zuri_chat_data'])
            ]
        );
    }
);

Flight::route(
    '/add_message/@id',
    function ($id) {
        $data = json_decode(file_get_contents(DATABASE_FILE));
        $chat = @$data->chats->{$id};
        if (!$chat) {
            Flight::redirect('/');
        }
        $message = new stdClass();
        $message->username = json_decode($_COOKIE['zuri_chat_data'], true)['username'];
        $message->message = $_POST['message'];
        $message->time = time();
        $chat->messages[] = $message;
        $chat->last_activity = time();
        $data->chats->{$id} = $chat;
        file_put_contents(DATABASE_FILE, json_encode($data));
        Flight::redirect('/chat/' . $id);
    }
);

Flight::route(
    '/end/@id',
    function ($id) {
        $data = json_decode(file_get_contents(DATABASE_FILE));

        if (!empty($data->chats->{$id})) {
            unset($data->chats->{$id});
            file_put_contents(DATABASE_FILE, json_encode($data));
            unset($_COOKIE['zuri_chat_data']);
            setcookie('zuri_chat_data', null, -1, '/');
            Flight::redirect('/');
        } else {
            Flight::redirect('/');
        }
    }
);
Flight::start();
