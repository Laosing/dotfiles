#!/bin/bash

cd "$(dirname "${BASH_SOURCE}")" && source "../utils.sh"

declare -a APT_PACKAGES=(

    # Tools for compiling/building software from source
    "build-essential"

    # GnuPG archive keys of the Debian archive
    # "debian-archive-keyring"

    # Software which is not included by default
    # in Ubuntu due to legal or copyright reasons
    #"ubuntu-restricted-extras"

    # Other
    # "atom"
    # "chromium-browser"
    "curl"
    # "firefox-trunk"
    # "flashplugin-installer"
    # "gimp"
    "git"
    # "google-chrome-unstable"
    # "imagemagick"
    # "nautilus-dropbox"
    # "opera"
    # "opera-next"
    # "transmission"
    # "vim-gnome"
    # "virtualbox"
    # "vlc"
    # "xclip"
    # "zopfli"
    "sublime-text-installer"
    "tilda"
    # "nodejs"
)

# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

add_key() {
    wget -qO - "$1" | sudo apt-key add - &> /dev/null
    #     │└─ write output to file
    #     └─ don't show output
}

add_ppa() {
    sudo add-apt-repository -y ppa:"$1" &> /dev/null
}

add_software_sources() {

    # Atom
    # [ $(cmd_exists "atom") -eq 1 ] \
    #     && add_ppa "webupd8team/atom"

    # Firefox Nightly
    # [ $(cmd_exists "firefox-trunk") -eq 1 ] \
        # && add_ppa "ubuntu-mozilla-daily/ppa"

    # Google Chrome
    # [ $(cmd_exists "google-chrome") -eq 1 ] \
        # && add_key "https://dl-ssl.google.com/linux/linux_signing_key.pub" \
        # && add_source_list \
                # "http://dl.google.com/linux/deb/ stable main" \
                # "google-chrome.list"

    # Opera & Opera Next
    # [ $(cmd_exists "opera") -eq 1 ] \
        # && add_key "http://deb.opera.com/archive.key" \
        # && add_source_list \
                # "http://deb.opera.com/opera/ stable non-free" \
                # "opera.list"

    # Sublime Text
    [ $(cmd_exists "sublime-text-installer") -eq 1 ] \
        && add_ppa "webupd8team/sublime-text-3"
}

add_curl_software() {
    # NodeJS
    [ $(cmd_exists "node") -eq 1 ] \
        && curl -sL https://deb.nodesource.com/setup | sudo bash -
        sudo apt-get install --allow-unauthenticated -qqy nodejs
        # && add_ppa "chris-lea/node.js"

    # Composer
    [ $(cmd_exists "composer") -eq 1 ] \
        && curl -sS https://getcomposer.org/installer | php
        sudo mv composer.phar /usr/local/bin/composer

    # RVM & Ruby
    [ $(cmd_exists "rvm") -eq 1 ] \
        && gpg -q --keyserver hkp://keys.gnupg.net --recv-keys 409B6B1796C275462A1703113804BB82D39DC0E3 \
        && \curl -sSL https://get.rvm.io | bash -s stable --ruby=1.9.3 >/dev/null 2>&1
}

add_source_list() {
    sudo sh -c "printf 'deb $1' >> '/etc/apt/sources.list.d/$2'"
}

install_package() {
    local q="${2:-$1}"

    if [ $(cmd_exists "$q") -eq 1 ]; then
        execute "sudo apt-get install --allow-unauthenticated -qqy $1" "$1"
        #                                      suppress output ─┘│
        #            assume "yes" as the answer to all prompts ──┘
    fi
}

remove_unneeded_packages() {

    # Remove packages that were automatically installed to satisfy
    # dependencies for other other packages and are no longer needed
    execute "sudo apt-get autoremove -qqy" "autoremove"

}

update_and_upgrade() {

    # Resynchronize the package index files from their sources
    execute "sudo apt-get update -y" "update"

    # Unstall the newest versions of all packages installed
    execute "sudo apt-get upgrade -qqy" "upgrade"

}

# - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

main() {

    local i=""

    add_software_sources
    update_and_upgrade

    printf "\n"

    for i in ${!APT_PACKAGES[*]}; do
        install_package "${APT_PACKAGES[$i]}"
    done

    printf "\n"

    add_curl_software
    update_and_upgrade
    remove_unneeded_packages

}

main
