<!doctype html>
<html lang="id" class="dark">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Active Directory User Explorer</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            brand: {
              50: "#f0f9ff",
              100: "#e0f2fe",
              500: "#0284c7",
              600: "#0284c7",
              700: "#0369a1",
              800: "#075985",
              900: "#0c4a6e",
            },
          },
        },
      },
    };
  </script>
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap");

    body {
      font-family: "Inter", sans-serif;
    }

    .code-font {
      font-family: "Fira Code", monospace;
    }
  </style>
</head>

<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col transition-colors duration-200">
  <header class="border-b border-slate-800 bg-slate-950/80 backdrop-blur sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <div class="p-2 bg-brand-600/20 text-brand-500 rounded-lg border border-brand-500/30">
          <i class="fa-solid fa-network-wired text-xl"></i>
        </div>
        <div>
          <h1 class="font-bold text-lg leading-tight tracking-wide text-white">
            AD User Explorer
          </h1>
          <p class="text-xs text-slate-400">
            Active Directory Management Console
          </p>
        </div>
      </div>

      <div class="flex items-center space-x-4">
        <div class="hidden md:flex items-center space-x-3 text-xs bg-slate-900 border border-slate-800 rounded-full px-3 py-1.5">
          <span class="flex items-center text-slate-300">
            <i class="fa-solid fa-server text-brand-500 mr-2"></i>
            <span id="connectedDomain">obi.com / obfpt.com / ad.lygend.com</span>
          </span>
          <span class="text-slate-600">|</span>
          <span class="flex items-center text-emerald-400">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse mr-2"></span>
            Domain Connected
          </span>
        </div>
      </div>
    </div>
  </header>

  <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <!-- Form Pencarian -->
    <section class="bg-slate-950 border border-slate-800 rounded-xl p-5 shadow-lg">
      <form id="searchForm" onsubmit="handleSearch(event)" class="space-y-4">
        <div class="flex flex-col sm:flex-row gap-3">
          <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <input
              type="text"
              id="usernameInput"
              class="w-full pl-10 pr-4 py-2.5 bg-slate-900 border border-slate-700 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-slate-100 placeholder-slate-500 outline-none transition font-mono text-sm"
              placeholder="Masukkan Awalan NIK / SAMAccountName (misal: D1124000)"
              value=""
              required />
          </div>

          <button
            type="submit"
            id="searchBtn"
            class="px-6 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-medium rounded-lg transition flex items-center justify-center gap-2 shadow-md shadow-brand-600/20 active:scale-95">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span>Cari User</span>
          </button>
        </div>
      </form>
    </section>

    <!-- Tabel Pilihan jika Ditemukan Banyak User -->
    <div id="multipleResults" class="hidden bg-slate-950 border border-slate-800 rounded-xl p-6 shadow-lg space-y-4">
      <div class="flex justify-between items-center border-b border-slate-800 pb-3">
        <h3 class="text-md font-semibold text-white flex items-center gap-2">
          <i class="fa-solid fa-list text-brand-500"></i>
          Ditemukan <span id="resultsCount" class="text-brand-400">0</span> User
        </h3>
        <p class="text-xs text-slate-400">Pilih user untuk melihat detail lengkap</p>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-300">
          <thead class="bg-slate-900 text-xs text-slate-400 uppercase tracking-wider">
            <tr>
              <th class="p-3">sAMAccountName / NIK</th>
              <th class="p-3">Nama Lengkap</th>
              <th class="p-3">Department</th>
              <th class="p-3">Domain</th>
              <th class="p-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody id="userTableBody" class="divide-y divide-slate-800"></tbody>
        </table>
      </div>
    </div>

    <!-- Dashboard Detail User -->
    <div id="userDashboard" class="hidden space-y-6">
      <button onclick="backToResults()" id="backBtn" class="hidden text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 px-3 py-1.5 rounded-lg border border-slate-700 transition flex items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Hasil
      </button>

      <!-- Overview Card -->
      <div class="bg-slate-950 border border-slate-800 rounded-xl p-6 shadow-lg relative overflow-hidden">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
          <div class="flex items-center space-x-5">
            <div class="relative">
              <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-brand-700 to-indigo-600 flex items-center justify-center text-2xl font-bold text-white shadow-inner" id="userAvatar">
                --
              </div>
              <span id="statusIndicator" class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 border-4 border-slate-950 rounded-full"></span>
            </div>

            <div>
              <div class="flex items-center space-x-3">
                <h2 class="text-2xl font-bold text-white" id="displayName">-</h2>
                <span id="accountStatusBadge" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                  Enabled
                </span>
              </div>
              <p class="text-sm text-slate-400 mt-0.5 font-mono" id="samAccountName">
                sAMAccountName: -
              </p>
              <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-300 mt-2">
                <span class="flex items-center"><i class="fa-solid fa-briefcase text-slate-500 mr-1.5"></i><span id="jobTitle">-</span></span>
                <span class="flex items-center"><i class="fa-solid fa-sitemap text-slate-500 mr-1.5"></i><span id="department">-</span></span>
                <span class="flex items-center"><i class="fa-solid fa-envelope text-slate-500 mr-1.5"></i><span id="emailAddr">-</span></span>
                <span class="flex items-center"><i class="fa-solid fa-phone text-slate-500 mr-1.5"></i><span id="phoneNum">-</span></span>
              </div>
            </div>
          </div>

          <div class="text-left md:text-right border-t md:border-t-0 pt-4 md:pt-0 border-slate-800">
            <span class="text-xs text-slate-500 block">Last Logon</span>
            <span class="text-sm text-slate-300 font-mono" id="lastLogon">-</span>
          </div>
        </div>
      </div>

      <!-- Detail Tabs -->
      <div class="bg-slate-950 border border-slate-800 rounded-xl overflow-hidden shadow-lg">
        <div class="flex border-b border-slate-800 bg-slate-900/50 overflow-x-auto">
          <button onclick="switchTab('general')" id="tab-general" class="tab-btn px-6 py-3.5 text-sm font-medium border-b-2 border-brand-500 text-brand-400 flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-id-card"></i> General Info
          </button>
          <button onclick="switchTab('groups')" id="tab-groups" class="tab-btn px-6 py-3.5 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-users"></i> Group Memberships (<span id="groupCount">0</span>)
          </button>
          <button onclick="switchTab('org')" id="tab-org" class="tab-btn px-6 py-3.5 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-building-user"></i> Organization & Manager
          </button>
        </div>

        <div class="p-6">
          <div id="content-general" class="tab-content space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="bg-slate-900/80 p-4 rounded-lg border border-slate-800">
                <span class="text-xs text-slate-500 font-medium uppercase tracking-wider block mb-1">Distinguished Name (DN)</span>
                <span class="text-xs code-font text-brand-300 break-all select-all" id="distinguishedName">-</span>
              </div>
              <div class="bg-slate-900/80 p-4 rounded-lg border border-slate-800">
                <span class="text-xs text-slate-500 font-medium uppercase tracking-wider block mb-1">User Principal Name (UPN)</span>
                <span class="text-sm code-font text-slate-200" id="userPrincipalName">-</span>
              </div>
              <div class="bg-slate-900/80 p-4 rounded-lg border border-slate-800">
                <span class="text-xs text-slate-500 font-medium uppercase tracking-wider block mb-1">Telephone Number</span>
                <span class="text-sm code-font text-slate-200" id="generalPhone">-</span>
              </div>
              <div class="bg-slate-900/80 p-4 rounded-lg border border-slate-800">
                <span class="text-xs text-slate-500 font-medium uppercase tracking-wider block mb-1">Password Last Set</span>
                <span class="text-sm code-font text-slate-200" id="pwdLastSet">-</span>
              </div>
              <div class="bg-slate-900/80 p-4 rounded-lg border border-slate-800">
                <span class="text-xs text-slate-500 font-medium uppercase tracking-wider block mb-1">Account Created Date</span>
                <span class="text-sm code-font text-slate-200" id="whenCreated">-</span>
              </div>
              <div class="bg-slate-900/80 p-4 rounded-lg border border-slate-800">
                <span class="text-xs text-slate-500 font-medium uppercase tracking-wider block mb-1">User Account Control (UAC)</span>
                <span class="text-sm code-font text-slate-200" id="userAccountControl">-</span>
              </div>
            </div>
          </div>

          <div id="content-groups" class="tab-content hidden space-y-3">
            <div class="grid grid-cols-1 gap-2" id="groupList"></div>
          </div>

          <div id="content-org" class="tab-content hidden space-y-6">
            <div>
              <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Manager</h3>
              <div class="bg-slate-900/80 border border-slate-800 p-4 rounded-lg">
                <p class="text-xs code-font text-brand-300 break-all" id="managerDn">-</p>
              </div>
            </div>
            <div>
              <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Direct Reports</h3>
              <div id="directReportsList" class="space-y-2"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- State Loading & Empty -->
    <div id="emptyState" class="bg-slate-950 border border-slate-800 rounded-xl p-12 text-center">
      <i class="fa-solid fa-user-gear text-4xl text-slate-600 mb-3"></i>
      <h3 class="text-lg font-semibold text-white">Pencarian User Active Directory</h3>
      <p class="text-sm text-slate-400 mt-1">Masukkan SAMAccountName / Awalan NIK di atas.</p>
    </div>

    <div id="loadingState" class="hidden bg-slate-950 border border-slate-800 rounded-xl p-12 text-center">
      <div class="inline-block animate-spin text-brand-500 text-3xl mb-4">
        <i class="fa-solid fa-circle-notch"></i>
      </div>
      <p class="text-sm font-medium text-slate-200">Mengambil data dari Active Directory...</p>
    </div>
  </main>

  <script>
    let globalUsersList = [];

    async function handleSearch(e) {
      e.preventDefault();
      const query = document.getElementById("usernameInput").value.trim();
      if (!query) return;

      showLoading();

      try {
        const response = await fetch(`get-user.php?username=${encodeURIComponent(query)}`);
        const data = await response.json();

        if (data.error) {
          alert("Informasi: " + data.error);
          showEmptyState();
          return;
        }

        globalUsersList = data.users;

        if (data.count === 1) {
          document.getElementById("backBtn").classList.add("hidden");
          renderSingleUserData(globalUsersList[0]);
        } else {
          renderMultipleUsersTable(globalUsersList);
        }
      } catch (err) {
        console.error(err);
        alert("Gagal terhubung ke endpoint get-user.php");
        showEmptyState();
      }
    }

    function renderMultipleUsersTable(users) {
      document.getElementById("loadingState").classList.add("hidden");
      document.getElementById("emptyState").classList.add("hidden");
      document.getElementById("userDashboard").classList.add("hidden");
      document.getElementById("multipleResults").classList.remove("hidden");

      document.getElementById("resultsCount").innerText = users.length;
      const tbody = document.getElementById("userTableBody");
      tbody.innerHTML = "";

      users.forEach((user, index) => {
        const samName = user.samaccountname || "-";
        const fullName = user.displayname || user.cn || "-";
        const dept = user.department || "-";
        const domain = user.source_domain || "-";

        tbody.innerHTML += `
          <tr class="hover:bg-slate-900/80 transition">
            <td class="p-3 font-mono font-semibold text-brand-400">${samName}</td>
            <td class="p-3 font-medium text-slate-100">${fullName}</td>
            <td class="p-3 text-slate-400">${dept}</td>
            <td class="p-3 text-slate-400"><span class="px-2 py-0.5 text-xs bg-slate-800 rounded border border-slate-700">${domain}</span></td>
            <td class="p-3 text-center">
              <button onclick="selectUser(${index})" class="px-3 py-1 bg-brand-600 hover:bg-brand-500 text-white text-xs rounded transition flex items-center gap-1 mx-auto">
                <i class="fa-solid fa-eye"></i> Detail
              </button>
            </td>
          </tr>
        `;
      });
    }

    function selectUser(index) {
      document.getElementById("multipleResults").classList.add("hidden");
      document.getElementById("backBtn").classList.remove("hidden");
      renderSingleUserData(globalUsersList[index]);
    }

    function backToResults() {
      document.getElementById("userDashboard").classList.add("hidden");
      document.getElementById("multipleResults").classList.remove("hidden");
    }

    function showLoading() {
      document.getElementById("userDashboard").classList.add("hidden");
      document.getElementById("multipleResults").classList.add("hidden");
      document.getElementById("emptyState").classList.add("hidden");
      document.getElementById("loadingState").classList.remove("hidden");
    }

    function showEmptyState() {
      document.getElementById("userDashboard").classList.add("hidden");
      document.getElementById("multipleResults").classList.add("hidden");
      document.getElementById("loadingState").classList.add("hidden");
      document.getElementById("emptyState").classList.remove("hidden");
      document.getElementById("connectedDomain").innerText = "obi.com / obfpt.com / ad.lygend.com";
    }

    function renderSingleUserData(data) {
      document.getElementById("loadingState").classList.add("hidden");
      document.getElementById("emptyState").classList.add("hidden");
      document.getElementById("userDashboard").classList.remove("hidden");

      document.getElementById("connectedDomain").innerText = data.source_domain || "obi.com";

      const memberOf = Array.isArray(data.memberof) ? data.memberof : data.memberof ? [data.memberof] : [];
      const directReports = Array.isArray(data.directreports) ? data.directreports : data.directreports ? [data.directreports] : [];
      const isEnabled = !(data.useraccountcontrol && parseInt(data.useraccountcontrol) & 2);

      const formattedUser = {
        SamAccountName: data.samaccountname || "-",
        DisplayName: data.displayname || data.cn || "-",
        Title: data.title || "-",
        Department: data.department || "-",
        EmailAddress: data.mail || "-",
        TelephoneNumber: data.telephonenumber || "-",
        Enabled: isEnabled,
        DistinguishedName: data.distinguishedname || "-",
        UserPrincipalName: data.userprincipalname || "-",
        UserAccountControl: data.useraccountcontrol || "-",
        PasswordLastSet: data.pwdlastset_formatted || data.pwdlastset || "-",
        WhenCreated: data.whencreated || "-",
        LastLogonDate: data.lastlogon_formatted || data.lastlogon || "-",
        ManagerDN: data.manager || "Tidak Ada Manager",
        DirectReports: directReports,
        MemberOf: memberOf,
      };

      const initials = formattedUser.DisplayName.split(" ")
        .map((n) => n[0])
        .join("")
        .substring(0, 2)
        .toUpperCase();

      document.getElementById("userAvatar").innerText = initials || "AD";
      document.getElementById("displayName").innerText = formattedUser.DisplayName;
      document.getElementById("samAccountName").innerText = `sAMAccountName: ${formattedUser.SamAccountName}`;
      document.getElementById("jobTitle").innerText = formattedUser.Title;
      document.getElementById("department").innerText = formattedUser.Department;
      document.getElementById("emailAddr").innerText = formattedUser.EmailAddress;
      document.getElementById("phoneNum").innerText = formattedUser.TelephoneNumber;
      document.getElementById("lastLogon").innerText = formattedUser.LastLogonDate;

      const statusBadge = document.getElementById("accountStatusBadge");
      const statusIndicator = document.getElementById("statusIndicator");
      if (formattedUser.Enabled) {
        statusBadge.className = "px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30";
        statusBadge.innerText = "Enabled";
        statusIndicator.className = "absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 border-4 border-slate-950 rounded-full";
      } else {
        statusBadge.className = "px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/30";
        statusBadge.innerText = "Disabled";
        statusIndicator.className = "absolute -bottom-1 -right-1 w-5 h-5 bg-rose-500 border-4 border-slate-950 rounded-full";
      }

      document.getElementById("distinguishedName").innerText = formattedUser.DistinguishedName;
      document.getElementById("userPrincipalName").innerText = formattedUser.UserPrincipalName;
      document.getElementById("generalPhone").innerText = formattedUser.TelephoneNumber;
      document.getElementById("pwdLastSet").innerText = formattedUser.PasswordLastSet;
      document.getElementById("whenCreated").innerText = formattedUser.WhenCreated;
      document.getElementById("userAccountControl").innerText = formattedUser.UserAccountControl;

      const groupList = document.getElementById("groupList");
      document.getElementById("groupCount").innerText = formattedUser.MemberOf.length;
      groupList.innerHTML = "";

      if (formattedUser.MemberOf.length > 0) {
        formattedUser.MemberOf.forEach((group) => {
          const groupCN = group.split(",")[0].replace("CN=", "");
          groupList.innerHTML += `
            <div class="bg-slate-900/80 border border-slate-800 p-3 rounded-lg flex items-center justify-between">
              <div class="flex items-center space-x-3 overflow-hidden">
                <i class="fa-solid fa-users-gear text-brand-500 text-sm flex-shrink-0"></i>
                <div class="truncate">
                  <span class="text-sm font-semibold text-slate-200 block">${groupCN}</span>
                  <span class="text-[11px] code-font text-slate-500 break-all">${group}</span>
                </div>
              </div>
            </div>
          `;
        });
      } else {
        groupList.innerHTML = `<div class="text-xs text-slate-500 italic">Tidak terdaftar di grup mana pun.</div>`;
      }

      document.getElementById("managerDn").innerText = formattedUser.ManagerDN;
      const reportsList = document.getElementById("directReportsList");
      reportsList.innerHTML = "";

      if (formattedUser.DirectReports.length > 0) {
        formattedUser.DirectReports.forEach((rep) => {
          reportsList.innerHTML += `
            <div class="bg-slate-900/80 border border-slate-800 p-3 rounded-lg flex items-center space-x-3">
              <i class="fa-solid fa-user text-slate-400 text-sm"></i>
              <span class="text-xs code-font text-slate-300 break-all">${rep}</span>
            </div>
          `;
        });
      } else {
        reportsList.innerHTML = `<div class="text-xs text-slate-500 italic">Tidak ada direct reports.</div>`;
      }
    }

    function switchTab(tabName) {
      document.querySelectorAll(".tab-content").forEach((el) => el.classList.add("hidden"));
      document.querySelectorAll(".tab-btn").forEach((btn) => {
        btn.className = "tab-btn px-6 py-3.5 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2 whitespace-nowrap";
      });

      document.getElementById(`content-${tabName}`).classList.remove("hidden");
      const activeBtn = document.getElementById(`tab-${tabName}`);
      activeBtn.className = "tab-btn px-6 py-3.5 text-sm font-medium border-b-2 border-brand-500 text-brand-400 flex items-center gap-2 whitespace-nowrap";
    }
  </script>
</body>

</html>