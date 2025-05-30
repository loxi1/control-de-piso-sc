<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com/3.4.16"></script>
  <title>Login</title>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-400 to-teal-500">

  <div class="relative bg-white/10 backdrop-blur-md rounded-[4rem] p-10 w-96 shadow-xl flex flex-col items-center">
    <!-- Icon -->
    <div class="bg-white/20 rounded-full p-4 mb-6">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A9.003 9.003 0 0112 15a9.003 9.003 0 016.879 2.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>
    </div>

    <!-- Sign In -->
    <h2 class="text-white text-2xl font-semibold mb-6">Sign In</h2>

    <!-- Username -->
    <div class="mb-4 w-full">
      <div class="flex items-center bg-white/20 rounded-full px-4 py-2 text-white">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 12a4 4 0 01-8 0m4-8a4 4 0 100 8 4 4 0 000-8zm0 12a8 8 0 00-8-8h16a8 8 0 00-8 8z" />
        </svg>
        <input type="text" placeholder="Username" class="bg-transparent outline-none flex-1 text-white placeholder-white/70" />
      </div>
    </div>

    <!-- Password -->
    <div class="mb-6 w-full">
      <div class="flex items-center bg-white/20 rounded-full px-4 py-2 text-white">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5s-3 1.343-3 3 1.343 3 3 3zm0 0v4" />
        </svg>
        <input type="password" placeholder="••••••••" class="bg-transparent outline-none flex-1 text-white placeholder-white/70" />
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white ml-2 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
      </div>
    </div>

    <!-- Login Button -->
    <button class="bg-cyan-700 hover:bg-cyan-800 text-white w-full py-2 rounded-full font-semibold transition">
      LOGIN
    </button>

    <!-- Footer -->
    <div class="flex justify-between text-white text-sm mt-4 w-full px-2">
      <label class="flex items-center gap-2">
        <input type="checkbox" class="accent-white" />
        Remember me
      </label>
      <a href="#" class="hover:underline">Forgot password?</a>
    </div>
  </div>

</body>
</html>
