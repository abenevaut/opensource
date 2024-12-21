# frozen_string_literal: true

require 'docker'
require 'dotenv'
require 'serverspec'
require 'json'

describe 'Dockerfile' do
  before(:all) do # rubocop:disable RSpec/BeforeAfterAll
    Docker.options[:read_timeout] = 1000
    Docker.options[:write_timeout] = 1000

    build_args = JSON.generate(
      VAPOR_DEFAULT_VERSION: ENV.fetch('VAPOR_DEFAULT_VERSION')
    )

    image = Docker::Image.build_from_dir(
      '.',
      t: 'ghcr.io/abenevaut/vapor-nginx:rspec',
      platform: ENV.fetch('DOCKER_DEFAULT_PLATFORM', 'linux/amd64'),
      buildargs: build_args
    )

    set :os, family: :alpine
    set :backend, :docker
    set :docker_image, image.id
  end

  after(:all) do # rubocop:disable RSpec/BeforeAfterAll
    # Reset the docker backend so other images/containers can be tested.
    Specinfra::Backend::Docker.clear
  end

  def php_version
    command('php -v').stdout
  end

  it 'installs php' do
    expect(php_version).to include('8.2').or include('8.3').or include('8.4')
  end

  describe package('nginx') do
    it { is_expected.to be_installed }
  end

  describe port(8080) do
    it { is_expected.to be_listening.with('tcp') }
  end

  describe port(9000) do
    it { is_expected.not_to be_listening }
  end
end
