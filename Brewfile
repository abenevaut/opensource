tap "homebrew/services"

cask_args appdir: "~/Applications", require_sha: true

brew "wget"
brew "sha3sum"
brew "git"
brew "git-lfs"
brew "composer"
brew "libyaml"
brew "pkg-config"
brew "zlib"
brew "vhs"
brew "imagemagick"
brew "protobuf@21"
brew "nvm"
brew "siege"
brew "terminal-notifier" if OS.mac?

#
# dnsmasq, nginx & php have to run as sudo user
#

brew "dnsmasq", restart_service: false, link: true
brew "emacs", restart_service: false, link: true
brew "mailhog", restart_service: true, link: true
brew "mysql", restart_service: true, link: true
brew "nginx", restart_service: false, link: true
brew "php", restart_service: false, link: false, conflicts_with: ["php@8.1", "php@8.2"]
brew "php@8.1", restart_service: false, link: false, conflicts_with: ["php", "php@8.2"]
brew "php@8.2", restart_service: false, link: true, conflicts_with: ["php", "php@8.1"]
brew "redis", restart_service: true, link: true
brew "supervisor", restart_service: true, link: true
