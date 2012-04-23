Vagrant::Config.run do |config|
  config.vm.box = "lucid-nfs_0.1_4.1.0"

  config.vm.network :hostonly, "33.33.33.126"

  config.ssh.max_tries = 100

  config.vm.customize [
    "modifyvm", :id,
    "--name", "Session VM",
    "--memory", "512"
  ]

  config.vm.share_folder "v-data", "/vagrant_data", "./"

  config.vm.provision :chef_solo do |chef|
    chef.cookbooks_path = "./../easybib-cookbooks"

    chef.add_recipe "php-fpm::install-apt"
    chef.add_recipe "php-fpm::pear"
    chef.add_recipe "phpunit"

    chef.log_level = :debug
  end

end
