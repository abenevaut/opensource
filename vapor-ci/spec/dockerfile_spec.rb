# frozen_string_literal: true

require 'docker'
require 'dotenv'
require 'serverspec'
require 'json'

describe 'Dockerfile' do
  before(:all) do # rubocop:disable RSpec/BeforeAfterAll
    ::Docker.options[:read_timeout] = 1000
    ::Docker.options[:write_timeout] = 1000

    Dotenv.load('./../.env.example')

    build_args = JSON.generate(
      'VAPOR_VERSION': ENV['VAPOR_VERSION'],
      'TAG_VAPOR_CI': ENV['TAG_VAPOR_CI'],
      'COMPOSER_HASH': ENV['COMPOSER_HASH']
    )

    image = ::Docker::Image.build_from_dir(
      '.',
      't': 'abenevaut/vapor-ci:rspec',
      'cache-from': 'abenevaut/vapor-ci:latest',
      'buildargs': build_args
    )

    set :os, family: :alpine
    set :backend, :docker
    set :docker_image, image.id
  end

  after(:all) do
    # Reset the docker backend so other images/containers can be tested.
    Specinfra::Backend::Docker.clear
  end

  describe file('/etc/os-release') do
    it { is_expected.to be_file }
  end

  describe command('cat /etc/os-release') do
    it 'confirm alpine version' do
      expect(subject.stdout).to match(/Alpine Linux/)
      expect(subject.stdout).to match(/3.18.4/)
    end
  end

  def php_version
    command('php -v').stdout
  end

  it 'installs php' do
    expect(php_version).to include('8.1').or include('8.2').or include('8.3')
  end

  def php_redis_loaded
    command('php -r "var_dump(extension_loaded(\'redis\'));"').stdout
  end

  it 'installs php-redis' do
    expect(php_redis_loaded).to include('true')
  end

  def php_gd_loaded
    command('php -r "var_dump(extension_loaded(\'gd\'));"').stdout
  end

  it 'installs php-gd' do
    expect(php_gd_loaded).to include('true')
  end

  def php_pdo_mysql_loaded
    command('php -r "var_dump(extension_loaded(\'pdo_mysql\'));"').stdout
  end

  it 'installs php-pdo_mysql' do
    expect(php_pdo_mysql_loaded).to include('true')
  end

  def php_intl_loaded
    command('php -r "var_dump(extension_loaded(\'intl\'));"').stdout
  end

  it 'installs php-intl' do
    expect(php_intl_loaded).to include('true')
  end

  def php_zip_loaded
    command('php -r "var_dump(extension_loaded(\'zip\'));"').stdout
  end

  it 'installs php-zip' do
    expect(php_zip_loaded).to include('true')
  end

  def php_xml_loaded
    command('php -r "var_dump(extension_loaded(\'xml\'));"').stdout
  end

  it 'installs php-xml' do
    expect(php_xml_loaded).to include('true')
  end

  def php_iconv_loaded
    command('php -r "var_dump(extension_loaded(\'iconv\'));"').stdout
  end

  it 'installs php-iconv' do
    expect(php_iconv_loaded).to include('true')
  end

  def php_soap_loaded
    command('php -r "var_dump(extension_loaded(\'soap\'));"').stdout
  end

  it 'installs php-soap' do
    expect(php_soap_loaded).to include('true')
  end

  def codecov_version
    command('codecov --version').stdout
  end

  it 'installs codecov' do
    expect(codecov_version).to include('0.7.1')
  end

  def php_pcov_loaded
    command('php -r "var_dump(extension_loaded(\'pcov\'));"').stdout
  end

  it 'installs php-pcov' do
    expect(php_pcov_loaded).to include('true')
  end

  def composer_version
    command('composer -V').stdout
  end

  it 'installs composer' do
    expect(composer_version).to include('2.6.5')
  end
end
