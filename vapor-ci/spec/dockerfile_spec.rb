# frozen_string_literal: true

require 'docker'
require 'dotenv'
require 'serverspec'
require 'json'

describe 'Dockerfile' do
  before(:all) do # rubocop:disable RSpec/BeforeAfterAll
    Docker.options[:read_timeout] = 1000
    Docker.options[:write_timeout] = 1000
    # Dotenv.load('./../.env.example')

    build_args = JSON.generate(
      VAPOR_DEFAULT_VERSION: ENV.fetch('VAPOR_DEFAULT_VERSION'),
      COMPOSER_HASH: ENV.fetch('COMPOSER_HASH')
    )

    image = Docker::Image.build_from_dir(
      '.',
      t: 'abenevaut/vapor-ci:rspec',
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
    expect(php_version).to include('8.3').or include('8.4').or include('8.5')
  end

  def php_xdebug_enabled
    command('php -r "var_dump(ini_get(\'xdebug.mode\'));"').stdout
  end

  it 'php-xdebug is enabled to coverage' do
    expect(php_xdebug_enabled).to include('coverage')
  end

  describe port(9000) do
    it { is_expected.not_to be_listening.with('tcp') }
  end

  def codecov_version
    command('codecov --version').stdout
  end

  it 'installs codecov' do
    expect(codecov_version).to include('0.8.0')
  end

  def composer_version
    command('composer -V').stdout
  end

  it 'installs composer' do
    expect(composer_version).to include('2.9.3')
  end
end
