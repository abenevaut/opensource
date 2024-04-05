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
      'TAG_VAPOR_ROADRUNNER': ENV['TAG_VAPOR_ROADRUNNER']
    )

    image = ::Docker::Image.build_from_dir(
      '.',
      't': 'abenevaut/vapor-roadrunner:rspec',
      'platform': ENV['DOCKER_DEFAULT_PLATFORM'],
      'buildargs': build_args
    )

    set :os, family: :alpine
    set :backend, :docker
    set :docker_image, image.id
  end

  after(:all) do # rubocop:disable RSpec/BeforeAfterAll
    # Reset the docker backend so other images/containers can be tested.
    Specinfra::Backend::Docker.clear
  end

  describe file('/etc/os-release') do
    it { is_expected.to be_file }
  end

  describe command('cat /etc/os-release') do
    it 'confirm alpine version' do
      expect(subject.stdout).to match(/Alpine Linux/)
      expect(subject.stdout).to match(/3.19.1/)
    end
  end

  def rr_version
    command('rr -v').stdout
  end

  it 'installs roadrunner' do
    expect(rr_version).to include('2023.3')
  end

  def php_version
    command('php -v').stdout
  end

  it 'installs php' do
    expect(php_version).to include('8.2').or include('8.3')
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

  def php_opcache_loaded
    command('php -r "var_dump(extension_loaded(\'Zend OPcache\'));"').stdout
  end

  it 'installs php-opcache' do
    expect(php_opcache_loaded).to include('false')
  end

  def php_opcache_enabled
    command('php -r "var_dump(ini_get(\'opcache.enable\'));"').stdout
  end

  it 'php-opcache is not enabled' do
    expect(php_opcache_enabled).not_to include('string(1) "1"')
  end

  def php_opcache_cli_enabled
    command('php -r "var_dump(ini_get(\'opcache.enable_cli\'));"').stdout
  end

  it 'php-opcache cli is not enabled' do
    expect(php_opcache_cli_enabled).not_to include('string(1) "1"')
  end
end
