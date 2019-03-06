# VM ip address

ip = "192.168.33.100"
env = { ip: ip, APP_URL: "http://" + ip + "/" }

Vagrant.configure("2") do |config|
    config.vm.box = "ubuntu/bionic64"

    config.vm.network "private_network", ip: ip

    config.vm.provider "virtualbox" do |vb|
        vb.memory = "2048"
    end

    config.vm.synced_folder ".", "/vagrant", disabled: true

    config.vm.synced_folder ".", "/var/www/app/", create: true

    config.vm.provision "shell", path: "./provisions/bootstrap.sh", env: env
    config.vm.provision "shell", path: "./provisions/init.sh", env: env
    config.vm.provision "shell", path: "./provisions/startup.sh", run: "always", env: env, privileged: false
end
