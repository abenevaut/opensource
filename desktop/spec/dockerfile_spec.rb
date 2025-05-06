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

    #     build_args = JSON.generate(
    #       VAPOR_DEFAULT_VERSION: ENV.fetch('VAPOR_DEFAULT_VERSION'),
    #       COMPOSER_HASH: ENV.fetch('COMPOSER_HASH')
    #     )

    image = Docker::Image.build_from_dir(
      '.',
      t: 'abenevaut/vapor-ci:rspec',
      platform: ENV.fetch('DOCKER_DEFAULT_PLATFORM', 'linux/amd64'),
      #       buildargs: build_args
    )

    set :os, family: :alpine
    set :backend, :docker
    set :docker_image, image.id
  end

  after(:all) do # rubocop:disable RSpec/BeforeAfterAll
    # Reset the docker backend so other images/containers can be tested.
    Specinfra::Backend::Docker.clear
  end

  def composer_version
    command('composer -V').stdout
  end

  it 'installs composer' do
    expect(composer_version).to include('2.8.6')
  end
end
