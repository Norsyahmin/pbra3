# Setting up your PBRA application on Ubuntu 24.04 LTS VPS with Docker

This guide provides detailed instructions to deploy your PBRA application using Docker containers on an Ubuntu 24.04 LTS Virtual Private Server (VPS). This setup will use a basic HTTP configuration.

## 1. Prerequisites on your Ubuntu 24.04 LTS VPS

Before you begin, ensure the following are in place on your Ubuntu 24.04 LTS VPS:

*   **Docker and Docker Compose:** The latest stable versions of Docker and Docker Compose should be installed. You can typically find up-to-date installation instructions on the official Docker documentation website.
    ```bash
    sudo apt update
    sudo apt install docker.io docker-compose -y
    sudo systemctl enable docker
    sudo systemctl start docker
    sudo usermod -aG docker $USER # Add your user to the docker group to run docker commands without sudo
    # You might need to log out and log back in for group changes to take effect:
    # `exit` from your current SSH session and reconnect.
    ```
*   **Domain DNS Configuration:** Your domain (`pbra.23ftt1869.com`) must have its DNS `A` record pointing to the public IP address of your Ubuntu VPS.
*   **Firewall Configuration (UFW - Uncomplicated Firewall):** If you are using `ufw`, ensure that port 80 (HTTP) is open to allow web traffic.
    ```bash
    sudo ufw allow 80/tcp
    sudo ufw enable # If ufw is not already enabled, run this command.
    sudo ufw status # Verify firewall rules
    ```
*   **Git (Optional but Recommended):** If you plan to clone your project directly to the VPS, Git should be installed.
    ```bash
    sudo apt install git -y
    ```

## 2. Deploying the Docker Containers

Follow these steps to deploy your application:

1.  **Transfer Project Files:** Copy your entire PBRA project directory to your Ubuntu VPS. A common method is using `git clone` or `scp`.
    ```bash
    # Example using git clone:
    git clone <your-repository-url> /path/to/your/project
    cd /path/to/your/project
    ```
    *Replace `/path/to/your/project` with the desired location on your VPS.*

2.  **Navigate to Project Root:** Change your current directory to the root of your PBRA project on the VPS, where `docker-compose.yml` is located.
    ```bash
    cd /path/to/your/project
    ```

3.  **Start Docker Compose:** From the project root directory, execute the following command to build your Docker images and start all services in detached mode (`-d`):
    ```bash
    docker-compose up -d --build
    ```
    This command will:
    *   Build the `php` service image using your `Dockerfile`.
    *   Start all defined services: `nginx`, `php`, `db` (MySQL), and `phpmyadmin`.
    *   Nginx will listen on port 80 (HTTP).

## 3. Verification

After completing the steps above, open a web browser and navigate to `http://pbra.23ftt1869.com`. You should see your application served over HTTP.

If you encounter any issues, check the Docker logs for the `nginx` container:
```bash
docker-compose logs nginx
```

### Initial Troubleshooting for "Connection refused"

If you are still experiencing "Connection refused" errors when trying to access your site, follow these debugging steps on your VPS:

**1. Stop and Remove All Existing Docker Containers and Networks:**
   *   Ensure you are in your project root directory.
     ```bash
     cd /path/to/your/project
     docker-compose down --volumes --rmi all
     ```
     *Note: `--volumes` will delete your database data. If your database contains important information, remove `--volumes` from the command.*

**2. Verify No Processes are Using Ports 80 on the Host System:**
   *   Check if any other service is running on your VPS and consuming port 80:
     ```bash
     sudo lsof -i :80
     ```
     *   If this returns any output, identify and stop that process (e.g., another Nginx instance, Apache, etc.).
     *   If you see services like `nginx` or `apache2` running directly on your host system outside of Docker, stop them:
       ```bash
       sudo systemctl stop nginx
       sudo systemctl stop apache2
       # You might also want to disable them so they don't restart automatically
       sudo systemctl disable nginx
       sudo systemctl disable apache2
       ```

**3. Re-verify Firewall Rules:**
   *   Double-check your firewall (UFW on Ubuntu, and any cloud provider firewalls) is allowing traffic on port 80.
     ```bash
     sudo ufw status verbose
     ```
     *   Ensure `80/tcp` is listed as `ALLOW IN Anywhere`. If not:
       ```bash
       sudo ufw allow 80/tcp
       sudo ufw reload
       ```
     *   **Crucially, check your cloud provider's network security groups/firewall settings.** Many VPS providers have an external firewall where you need to explicitly open ports.

**4. Start Docker Compose Again:**
   *   Ensure you are in your project root directory.
     ```bash
     cd /path/to/your/project
     docker-compose up -d --build
     ```

**5. Immediately Check Nginx Status and Logs:**
   *   After starting, confirm if Nginx is running and inspect its logs for any errors during startup.
     ```bash
     docker-compose ps
     docker-compose logs nginx
     ```
     *   Look for any `ERROR` or `FAIL` messages in the Nginx logs. Pay attention to warnings about binding to ports.

**6. Test Internal Nginx Connectivity:**
   *   From your VPS, try to access the Nginx endpoint again to confirm internal access.
     ```bash
     curl http://localhost
     ```
     *   This should return HTML content from your application. If this *still* fails, Nginx inside Docker is not starting correctly or is misconfigured.

If you encounter further issues, please provide the complete output of `docker-compose ps` and `docker-compose logs nginx`.
