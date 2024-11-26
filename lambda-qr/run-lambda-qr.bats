setup() {
    load 'bats-support/load'
    load 'bats-assert/load'
}

@test "Generate QR Code with required parameters" {
    run serverless bref:local -f qr --data '{"correction": "L", "format": "png", "size": 100, "text": "1234"}'
    [ "$status" -eq 0 ]
}

@test "Generate QR Code with 'image' parameter" {
    run serverless bref:local -f qr --data '{"correction": "L", "format": "png", "size": 100, "text": "1234", "image": "https://avatars.githubusercontent.com/u/1134021?s=48&v=4"}'
    [ "$status" -eq 0 ]
}

@test "Forgot 'format' parameter" {
    run serverless bref:local -f qr --data '{"correction": "L"}'
    [ "$status" -eq 1 ]
    assert_output --partial 'The format field is required.'
}
