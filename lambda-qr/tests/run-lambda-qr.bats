setup() {
    load 'test_helper/bats-support/load'
    load 'test_helper/bats-assert/load'
}

@test "can run our script" {
    serverless bref:local -f qr --data '{"correction": "L", "format": "png", "size": 100, "text": "1234"}'
    assert_output --partial '=='
}

@test "can run our script" {
    serverless bref:local -f qr --data '{"correction": "L", "format": "png", "size": 100, "text": "1234", "image": "https://www.abenevaut.dev/favicon/android-chrome-96x96.png"}'
    assert_output --partial '=='
}

@test "can run our script" {
    serverless bref:local -f qr --data '{"correction": "L"}'
    assert_output --partial 'The format field is required.'
}
