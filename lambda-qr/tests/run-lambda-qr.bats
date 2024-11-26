setup() {
    load 'bats-support/load'
    load 'bats-assert/load'
}

@test "Generate QR Code with required parameters" {
    serverless bref:local -f qr --data '{"correction": "L", "format": "png", "size": 100, "text": "1234"}'
    assert_output --partial '=='
}

@test "Generate QR Code with 'image' parameter" {
    serverless bref:local -f qr --data '{"correction": "L", "format": "png", "size": 100, "text": "1234", "image": "https://www.abenevaut.dev/favicon/android-chrome-96x96.png"}'
    assert_output --partial '=='
}

@test "Forgot 'format' parameter" {
    serverless bref:local -f qr --data '{"correction": "L"}'
    assert_output --partial 'The format field is required.'
}
