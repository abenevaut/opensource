# frozen_string_literal: true

require 'docker'
require 'serverspec'

describe 'Dockerfile' do
  before(:all) do # rubocop:disable RSpec/BeforeAfterAll
    Docker.options[:read_timeout] = 1000
    Docker.options[:write_timeout] = 1000

    image = Docker::Image.build_from_dir(
      '.',
      t: 'abenevaut/vapor-ci:rspec',
      platform: ENV.fetch('DOCKER_DEFAULT_PLATFORM', 'linux/amd64')
    )

    set :os, family: :alpine
    set :backend, :docker
    set :docker_image, image.id
  end

  after(:all) do # rubocop:disable RSpec/BeforeAfterAll
    # Reset the docker backend so other images/containers can be tested.
    Specinfra::Backend::Docker.clear
  end

  def uv_version
    command('uv --version').stdout
  end

  it 'installs uv' do
    expect(uv_version).to include('0.11')
  end

  def copilot_version
    command('copilot -v').stdout
  end

  it 'installs copilot' do
    expect(copilot_version).to include('1')
  end
end
