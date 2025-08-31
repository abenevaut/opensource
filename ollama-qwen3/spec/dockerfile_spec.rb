# frozen_string_literal: true

require 'docker'
require 'dotenv'
require 'serverspec'
require 'json'

describe 'Dockerfile' do
  before(:all) do # rubocop:disable RSpec/BeforeAfterAll
    Docker.options[:read_timeout] = 1000
    Docker.options[:write_timeout] = 1000

    image = Docker::Image.build_from_dir(
      '.',
      t: 'abenevaut/test-qwen3-0.6b:rspec'
    )

    set :os, family: :alpine
    set :backend, :docker
    set :docker_image, image.id
  end

  after(:all) do # rubocop:disable RSpec/BeforeAfterAll
    # Reset the docker backend so other images/containers can be tested.
    Specinfra::Backend::Docker.clear
  end

  def curl_version
    command('command -v curl').exit_status
  end

  it 'installs curl' do
    expect(curl_version).to eq 0
  end
end
