<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold gradient-text font-display">Chat Management</h1>
            <p class="text-secondary-600 mt-2">Monitor and manage chat rooms and messages</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="showCreateRoomModal = true"
              class="btn-primary flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              <span>Create Room</span>
            </button>
            <button
              @click="exportData"
              class="btn-secondary flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <span>Export</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="card">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm text-secondary-600">Total Rooms</p>
              <p class="text-2xl font-bold text-secondary-900">{{ stats.total_rooms || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm text-secondary-600">Total Messages</p>
              <p class="text-2xl font-bold text-secondary-900">{{ stats.total_messages || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm text-secondary-600">Active Users</p>
              <p class="text-2xl font-bold text-secondary-900">{{ stats.active_users || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-orange-100 text-orange-600 mr-4">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div>
              <p class="text-sm text-secondary-600">Today's Messages</p>
              <p class="text-2xl font-bold text-secondary-900">{{ stats.today_messages || 0 }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="mb-6">
        <div class="border-b border-secondary-200">
          <nav class="-mb-px flex space-x-8">
            <button
              @click="activeTab = 'rooms'"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-sm',
                activeTab === 'rooms'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300'
              ]"
            >
              Chat Rooms
            </button>
            <button
              @click="activeTab = 'messages'"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-sm',
                activeTab === 'messages'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300'
              ]"
            >
              Messages
            </button>
            <button
              @click="activeTab = 'reports'"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-sm',
                activeTab === 'reports'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300'
              ]"
            >
              Reports
            </button>
          </nav>
        </div>
      </div>

      <!-- Chat Rooms Tab -->
      <div v-if="activeTab === 'rooms'">
        <!-- Filters -->
        <div class="card mb-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
              <!-- Search -->
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
                <input
                  v-model="roomFilters.search"
                  @input="debouncedSearchRooms"
                  type="text"
                  class="pl-10 pr-4 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  placeholder="Search rooms..."
                />
              </div>

              <!-- Type Filter -->
              <select
                v-model="roomFilters.type"
                @change="fetchRooms"
                class="px-4 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              >
                <option value="">All Types</option>
                <option value="group">Group</option>
                <option value="private">Private</option>
              </select>

              <!-- Status Filter -->
              <select
                v-model="roomFilters.status"
                @change="fetchRooms"
                class="px-4 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              >
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <!-- Bulk Actions -->
            <div class="flex items-center space-x-2" v-if="selectedRooms.length > 0">
              <span class="text-sm text-secondary-600">{{ selectedRooms.length }} selected</span>
              <button
                @click="bulkRoomAction('activate')"
                class="px-3 py-1 bg-green-100 text-green-700 rounded-md hover:bg-green-200 transition-colors"
              >
                Activate
              </button>
              <button
                @click="bulkRoomAction('deactivate')"
                class="px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors"
              >
                Deactivate
              </button>
            </div>
          </div>
        </div>

        <!-- Rooms Table -->
        <div class="card">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-secondary-200">
              <thead class="bg-secondary-50">
                <tr>
                  <th class="px-6 py-3 text-left">
                    <input
                      type="checkbox"
                      @change="toggleSelectAllRooms"
                      :checked="selectedRooms.length === (rooms?.length || 0) && (rooms?.length || 0) > 0"
                      class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                    />
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Room
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Type
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Participants
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Messages
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Created
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-secondary-200">
                <!-- Loading State -->
                <tr v-if="loading">
                  <td colspan="8" class="px-6 py-12 text-center">
                    <div class="flex items-center justify-center">
                      <svg class="animate-spin h-8 w-8 text-primary-600 mr-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <span class="text-secondary-600">Loading chat rooms...</span>
                    </div>
                  </td>
                </tr>

                <!-- Empty State -->
                <tr v-else-if="!rooms || rooms.length === 0">
                  <td colspan="8" class="px-6 py-12 text-center">
                    <div class="text-secondary-500">
                      <svg class="mx-auto h-12 w-12 text-secondary-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                      </svg>
                      <p class="text-lg font-medium text-secondary-900 mb-2">No chat rooms found</p>
                      <p class="text-secondary-500">Get started by creating your first chat room.</p>
                    </div>
                  </td>
                </tr>

                <!-- Data Rows -->
                <tr v-else v-for="room in rooms" :key="room.id" class="hover:bg-secondary-50">
                  <td class="px-6 py-4">
                    <input
                      type="checkbox"
                      :value="room.id"
                      v-model="selectedRooms"
                      class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                    />
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-primary-500 to-accent-500 flex items-center justify-center text-white font-medium">
                          {{ room.name.charAt(0).toUpperCase() }}
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-secondary-900">{{ room.name }}</div>
                        <div class="text-sm text-secondary-500">{{ room.description || 'No description' }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span :class="getTypeBadgeClass(room.type)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ room.type }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-secondary-900">
                    {{ room.participants_count || 0 }}
                  </td>
                  <td class="px-6 py-4 text-sm text-secondary-900">
                    {{ room.messages_count || 0 }}
                  </td>
                  <td class="px-6 py-4">
                    <span :class="getStatusBadgeClass(room.is_active)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ room.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-secondary-500">
                    {{ formatDate(room.created_at) }}
                  </td>
                  <td class="px-6 py-4 text-sm font-medium space-x-2">
                    <button
                      @click="viewRoom(room)"
                      class="text-primary-600 hover:text-primary-900"
                    >
                      View
                    </button>
                    <button
                      @click="editRoom(room)"
                      class="text-primary-600 hover:text-primary-900"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteRoom(room)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Messages Tab -->
      <div v-if="activeTab === 'messages'">
        <!-- Message Filters -->
        <div class="card mb-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
              <!-- Search -->
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
                <input
                  v-model="messageFilters.search"
                  @input="debouncedSearchMessages"
                  type="text"
                  class="pl-10 pr-4 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  placeholder="Search messages..."
                />
              </div>

              <!-- Room Filter -->
              <select
                v-model="messageFilters.room_id"
                @change="fetchMessages"
                class="px-4 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              >
                <option value="">All Rooms</option>
                <option v-for="room in rooms" :key="room.id" :value="room.id">{{ room.name }}</option>
              </select>

              <!-- Date Range -->
              <input
                v-model="messageFilters.date_from"
                @change="fetchMessages"
                type="date"
                class="px-4 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              />
              <input
                v-model="messageFilters.date_to"
                @change="fetchMessages"
                type="date"
                class="px-4 py-2 border border-secondary-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              />
            </div>

            <!-- Bulk Actions -->
            <div class="flex items-center space-x-2" v-if="selectedMessages.length > 0">
              <span class="text-sm text-secondary-600">{{ selectedMessages.length }} selected</span>
              <button
                @click="bulkDeleteMessages(false)"
                class="px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors"
              >
                Delete
              </button>
              <button
                @click="bulkDeleteMessages(true)"
                class="px-3 py-1 bg-red-200 text-red-800 rounded-md hover:bg-red-300 transition-colors"
              >
                Delete Permanently
              </button>
            </div>
          </div>
        </div>

        <!-- Messages Table -->
        <div class="card">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-secondary-200">
              <thead class="bg-secondary-50">
                <tr>
                  <th class="px-6 py-3 text-left">
                    <input
                      type="checkbox"
                      @change="toggleSelectAllMessages"
                      :checked="selectedMessages.length === (messages?.length || 0) && (messages?.length || 0) > 0"
                      class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                    />
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Message
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    User
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Room
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Type
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Created
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-secondary-200">
                <!-- Loading State -->
                <tr v-if="loading">
                  <td colspan="7" class="px-6 py-12 text-center">
                    <div class="flex items-center justify-center">
                      <svg class="animate-spin h-8 w-8 text-primary-600 mr-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <span class="text-secondary-600">Loading messages...</span>
                    </div>
                  </td>
                </tr>

                <!-- Empty State -->
                <tr v-else-if="!messages || messages.length === 0">
                  <td colspan="7" class="px-6 py-12 text-center">
                    <div class="text-secondary-500">
                      <svg class="mx-auto h-12 w-12 text-secondary-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                      </svg>
                      <p class="text-lg font-medium text-secondary-900 mb-2">No messages found</p>
                      <p class="text-secondary-500">Try adjusting your search filters.</p>
                    </div>
                  </td>
                </tr>

                <!-- Data Rows -->
                <tr v-else v-for="message in messages" :key="message.id" class="hover:bg-secondary-50">
                  <td class="px-6 py-4">
                    <input
                      type="checkbox"
                      :value="message.id"
                      v-model="selectedMessages"
                      class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                    />
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-secondary-900">
                      {{ message.content || 'No content' }}
                    </div>
                    <div class="text-xs text-secondary-500" v-if="message.is_system">
                      System message
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-8 w-8">
                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-primary-500 to-accent-500 flex items-center justify-center text-white font-medium text-xs">
                          {{ message.user ? message.user.name.charAt(0).toUpperCase() : 'S' }}
                        </div>
                      </div>
                      <div class="ml-3">
                        <div class="text-sm font-medium text-secondary-900">
                          {{ message.user ? message.user.name : 'System' }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-secondary-900">
                      {{ message.chat_room ? message.chat_room.name : 'Unknown' }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span :class="getMessageTypeBadgeClass(message.type)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ message.type }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-secondary-500">
                    {{ formatDate(message.created_at) }}
                  </td>
                  <td class="px-6 py-4 text-sm font-medium space-x-2">
                    <button
                      @click="viewMessage(message)"
                      class="text-primary-600 hover:text-primary-900"
                    >
                      View
                    </button>
                    <button
                      @click="deleteMessage(message)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Reports Tab -->
      <div v-if="activeTab === 'reports'">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Chat Activity Chart -->
          <div class="card">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-secondary-900">Chat Activity (Last 30 Days)</h3>
              <button
                @click="createChatActivityChart"
                class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                Refresh Chart
              </button>
            </div>
            <div class="h-64 relative bg-gray-50 rounded-lg overflow-hidden">
              <!-- Chart.js Canvas -->
              <canvas
                ref="chatActivityChart"
                id="chatActivityChart"
                class="w-full h-full absolute inset-0"
                style="display: block; box-sizing: border-box;"
              ></canvas>

              <!-- Fallback SVG Chart -->
              <div v-if="!chatActivityChartInstance" class="absolute inset-0 p-4">
                <svg class="w-full h-full" viewBox="0 0 800 200">
                  <!-- Grid lines -->
                  <defs>
                    <pattern id="grid" width="40" height="20" patternUnits="userSpaceOnUse">
                      <path d="M 40 0 L 0 0 0 20" fill="none" stroke="#e5e7eb" stroke-width="1"/>
                    </pattern>
                  </defs>
                  <rect width="100%" height="100%" fill="url(#grid)" />

                  <!-- Sample data line -->
                  <polyline
                    fill="none"
                    stroke="#3b82f6"
                    stroke-width="2"
                    points="40,150 80,120 120,140 160,100 200,110 240,80 280,90 320,70 360,85 400,60 440,75 480,50 520,65 560,45 600,55 640,40 680,50 720,35 760,45"
                  />

                  <!-- Data points -->
                  <circle cx="40" cy="150" r="3" fill="#3b82f6"/>
                  <circle cx="80" cy="120" r="3" fill="#3b82f6"/>
                  <circle cx="120" cy="140" r="3" fill="#3b82f6"/>
                  <circle cx="160" cy="100" r="3" fill="#3b82f6"/>
                  <circle cx="200" cy="110" r="3" fill="#3b82f6"/>
                  <circle cx="240" cy="80" r="3" fill="#3b82f6"/>
                  <circle cx="280" cy="90" r="3" fill="#3b82f6"/>
                  <circle cx="320" cy="70" r="3" fill="#3b82f6"/>
                  <circle cx="360" cy="85" r="3" fill="#3b82f6"/>
                  <circle cx="400" cy="60" r="3" fill="#3b82f6"/>
                  <circle cx="440" cy="75" r="3" fill="#3b82f6"/>
                  <circle cx="480" cy="50" r="3" fill="#3b82f6"/>
                  <circle cx="520" cy="65" r="3" fill="#3b82f6"/>
                  <circle cx="560" cy="45" r="3" fill="#3b82f6"/>
                  <circle cx="600" cy="55" r="3" fill="#3b82f6"/>
                  <circle cx="640" cy="40" r="3" fill="#3b82f6"/>
                  <circle cx="680" cy="50" r="3" fill="#3b82f6"/>
                  <circle cx="720" cy="35" r="3" fill="#3b82f6"/>
                  <circle cx="760" cy="45" r="3" fill="#3b82f6"/>

                  <!-- Y-axis labels -->
                  <text x="10" y="25" font-size="12" fill="#6b7280">100</text>
                  <text x="10" y="75" font-size="12" fill="#6b7280">50</text>
                  <text x="10" y="125" font-size="12" fill="#6b7280">25</text>
                  <text x="10" y="175" font-size="12" fill="#6b7280">0</text>
                </svg>

                <!-- Loading overlay -->
                <div v-if="!chartData.labels.length" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
                  <div class="text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
                    <p class="text-sm text-gray-500">Loading chart data...</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Top Users -->
          <div class="card">
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">Most Active Users</h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between p-3 bg-secondary-50 rounded-lg">
                <div class="flex items-center">
                  <div class="h-8 w-8 rounded-full bg-gradient-to-r from-primary-500 to-accent-500 flex items-center justify-center text-white font-medium text-xs">
                    A
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-secondary-900">Admin</div>
                  </div>
                </div>
                <div class="text-sm text-secondary-500">150 messages</div>
              </div>
              <div class="flex items-center justify-between p-3 bg-secondary-50 rounded-lg">
                <div class="flex items-center">
                  <div class="h-8 w-8 rounded-full bg-gradient-to-r from-primary-500 to-accent-500 flex items-center justify-center text-white font-medium text-xs">
                    T
                  </div>
                  <div class="ml-3">
                    <div class="text-sm font-medium text-secondary-900">Test User</div>
                  </div>
                </div>
                <div class="text-sm text-secondary-500">120 messages</div>
              </div>
            </div>
          </div>

          <!-- Room Statistics -->
          <div class="card">
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">Room Statistics</h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center p-3 bg-secondary-50 rounded-lg">
                <span class="text-sm text-secondary-600">Most Active Room</span>
                <span class="text-sm font-medium text-secondary-900">General Discussion</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-secondary-50 rounded-lg">
                <span class="text-sm text-secondary-600">Average Messages per Room</span>
                <span class="text-sm font-medium text-secondary-900">{{ Math.round(stats.total_messages / stats.total_rooms) || 0 }}</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-secondary-50 rounded-lg">
                <span class="text-sm text-secondary-600">Private vs Group Rooms</span>
                <span class="text-sm font-medium text-secondary-900">{{ getPrivateVsGroupRatio() }}</span>
              </div>
            </div>
          </div>

          <!-- System Health -->
          <div class="card">
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">System Health</h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                <span class="text-sm text-green-600">Database Status</span>
                <span class="text-sm font-medium text-green-900">✅ Healthy</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                <span class="text-sm text-green-600">WebSocket Status</span>
                <span class="text-sm font-medium text-green-900">✅ Connected</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                <span class="text-sm text-yellow-600">Queue Status</span>
                <span class="text-sm font-medium text-yellow-900">⚠️ Monitoring</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useAdminStore } from '../../stores/admin';
import { useNotificationStore } from '../../stores/notifications';
import { debounce } from 'lodash-es';
import Chart from 'chart.js/auto';

const adminStore = useAdminStore();
const notificationStore = useNotificationStore();

const loading = ref(false);
const activeTab = ref('rooms');
const showCreateRoomModal = ref(false);

// Chat Rooms
const rooms = ref([]);
const selectedRooms = ref([]);
const roomFilters = reactive({
  search: '',
  type: '',
  status: ''
});

// Messages
const messages = ref([]);
const selectedMessages = ref([]);
const messageFilters = reactive({
  search: '',
  room_id: '',
  user_id: '',
  date_from: '',
  date_to: ''
});

// Stats
const stats = ref({
  total_rooms: 0,
  total_messages: 0,
  active_users: 0,
  today_messages: 0
});

// Chart
const chatActivityChart = ref(null);
const chatActivityChartInstance = ref(null);
const chartData = ref({
  labels: [],
  datasets: [{
    label: 'Messages',
    data: [],
    borderColor: 'rgb(59, 130, 246)',
    backgroundColor: 'rgba(59, 130, 246, 0.1)',
    tension: 0.4,
    fill: true
  }]
});

const fetchRooms = async () => {
  try {
    loading.value = true;
    console.log('🔄 Fetching chat rooms with filters:', roomFilters);

    const response = await adminStore.fetchChatRooms(roomFilters);
    console.log('📊 API Response:', response);

    if (response.success) {
      // Handle both paginated and non-paginated responses
      if (response.pagination && response.pagination.data) {
        rooms.value = Array.isArray(response.pagination.data) ? response.pagination.data : [];
        console.log('✅ Rooms loaded (paginated):', rooms.value.length);
      } else if (response.data) {
        rooms.value = Array.isArray(response.data) ? response.data : [];
        console.log('✅ Rooms loaded (direct):', rooms.value.length);
      } else {
        rooms.value = [];
        console.log('⚠️ No data in response');
      }
      updateStats();
    } else {
      rooms.value = [];
      console.error('❌ API Error:', response.message);
      notificationStore.error('Error', response.message || 'Failed to fetch chat rooms');
    }
  } catch (error) {
    console.error('❌ Fetch error:', error);
    rooms.value = [];
    notificationStore.error('Error', 'Failed to fetch chat rooms');
  } finally {
    loading.value = false;
  }
};

const fetchMessages = async () => {
  try {
    loading.value = true;
    console.log('🔄 Fetching messages with filters:', messageFilters);

    // Convert reactive object to plain object for API call
    const params = {
      search: messageFilters.search || '',
      room_id: messageFilters.room_id || '',
      user_id: messageFilters.user_id || '',
      date_from: messageFilters.date_from || '',
      date_to: messageFilters.date_to || '',
      per_page: 50
    };

    // Remove empty params
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null || params[key] === undefined) {
        delete params[key];
      }
    });

    console.log('📤 Cleaned params:', params);

    const response = await adminStore.fetchMessages(params);
    console.log('📊 Messages API Response:', response);

    if (response.success) {
      // Handle both paginated and non-paginated responses
      if (response.pagination && response.pagination.data) {
        messages.value = Array.isArray(response.pagination.data) ? response.pagination.data : [];
        console.log('✅ Messages loaded (paginated):', messages.value.length);
      } else if (response.data) {
        messages.value = Array.isArray(response.data) ? response.data : [];
        console.log('✅ Messages loaded (direct):', messages.value.length);
      } else {
        messages.value = [];
        console.log('⚠️ No messages data in response');
      }
    } else {
      messages.value = [];
      console.error('❌ Messages API Error:', response.message);
      notificationStore.error('Error', response.message || 'Failed to fetch messages');
    }
  } catch (error) {
    console.error('❌ Messages fetch error:', error);
    messages.value = [];
    notificationStore.error('Error', 'Failed to fetch messages');
  } finally {
    loading.value = false;
  }
};

const debouncedSearchMessages = debounce(() => {
  console.log('🔍 Debounced search triggered');
  fetchMessages();
}, 300);

const toggleSelectAllMessages = () => {
  const messagesArray = Array.isArray(messages.value) ? messages.value : [];

  if (selectedMessages.value.length === messagesArray.length && messagesArray.length > 0) {
    selectedMessages.value = [];
  } else {
    selectedMessages.value = messagesArray.map(message => message.id);
  }
};

const getMessageTypeBadgeClass = (type) => {
  const classes = {
    'text': 'bg-blue-100 text-blue-800',
    'image': 'bg-green-100 text-green-800',
    'file': 'bg-purple-100 text-purple-800',
    'audio': 'bg-yellow-100 text-yellow-800',
  };
  return classes[type] || 'bg-gray-100 text-gray-800';
};

const viewMessage = (message) => {
  notificationStore.info('Info', `View message: ${message.content?.substring(0, 50)}...`);
};

const deleteMessage = async (message) => {
  if (confirm(`Are you sure you want to delete this message?`)) {
    try {
      const result = await adminStore.deleteMessage(message.chat_room_id, message.id);
      if (result.success) {
        notificationStore.success('Success', 'Message deleted successfully');
        fetchMessages();
      } else {
        notificationStore.error('Error', result.message);
      }
    } catch (error) {
      notificationStore.error('Error', 'Failed to delete message');
    }
  }
};

const bulkDeleteMessages = async (permanent = false) => {
  if (selectedMessages.value.length === 0) return;

  const action = permanent ? 'permanently delete' : 'delete';
  if (confirm(`Are you sure you want to ${action} ${selectedMessages.value.length} message(s)?`)) {
    try {
      // Group messages by chat room
      const messagesByRoom = {};
      selectedMessages.value.forEach(messageId => {
        const message = messages.value.find(m => m.id === messageId);
        if (message) {
          if (!messagesByRoom[message.chat_room_id]) {
            messagesByRoom[message.chat_room_id] = [];
          }
          messagesByRoom[message.chat_room_id].push(messageId);
        }
      });

      let totalDeleted = 0;
      for (const [chatRoomId, messageIds] of Object.entries(messagesByRoom)) {
        const result = await adminStore.bulkDeleteMessages(chatRoomId, messageIds, permanent);
        if (result.success) {
          totalDeleted += result.affectedCount;
        }
      }

      notificationStore.success('Success', `${totalDeleted} message(s) ${action}d successfully`);
      selectedMessages.value = [];
      fetchMessages();
    } catch (error) {
      notificationStore.error('Error', `Failed to ${action} messages`);
    }
  }
};

const getPrivateVsGroupRatio = () => {
  const roomsArray = Array.isArray(rooms.value) ? rooms.value : [];
  const privateCount = roomsArray.filter(room => room.type === 'private').length;
  const groupCount = roomsArray.filter(room => room.type === 'group').length;
  return `${privateCount} private / ${groupCount} group`;
};

const updateStats = () => {
  const roomsArray = Array.isArray(rooms.value) ? rooms.value : [];
  console.log('📊 Updating stats for', roomsArray.length, 'rooms');

  stats.value = {
    total_rooms: roomsArray.length,
    total_messages: roomsArray.reduce((sum, room) => sum + (room.messages_count || 0), 0),
    active_users: roomsArray.reduce((sum, room) => sum + (room.participants_count || 0), 0),
    today_messages: roomsArray.reduce((sum, room) => sum + (room.messages_today || 0), 0)
  };

  console.log('📈 Updated stats:', stats.value);
};

// Chart functions
const fetchChatActivityData = async () => {
  console.log('📊 Fetching chat activity data...');

  try {
    // Generate last 30 days data
    const days = [];
    const messageCounts = [];
    const today = new Date();

    console.log('📅 Generating data for last 30 days...');

    for (let i = 29; i >= 0; i--) {
      const date = new Date(today);
      date.setDate(date.getDate() - i);

      const dayLabel = date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
      });
      days.push(dayLabel);

      // Simulate message count data (in real app, this would come from API)
      // For now, generate some realistic looking data
      const baseCount = Math.floor(Math.random() * 50) + 10;
      const weekendMultiplier = date.getDay() === 0 || date.getDay() === 6 ? 0.7 : 1;
      const finalCount = Math.floor(baseCount * weekendMultiplier);
      messageCounts.push(finalCount);
    }

    console.log('📈 Generated labels:', days);
    console.log('📊 Generated data:', messageCounts);

    chartData.value = {
      labels: days,
      datasets: [{
        label: 'Messages',
        data: messageCounts,
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
        fill: true,
        pointBackgroundColor: 'rgb(59, 130, 246)',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6
      }]
    };

    console.log('✅ Chart data prepared:', chartData.value);
    return true;
  } catch (error) {
    console.error('❌ Error fetching chat activity data:', error);
    return false;
  }
};

const createChatActivityChart = async () => {
  console.log('🎯 Creating chat activity chart...');
  console.log('🎯 Canvas ref:', chatActivityChart.value);
  console.log('🎯 Chart.js available:', typeof Chart);

  if (!chatActivityChart.value) {
    console.error('❌ Canvas element not found');
    // Try to find canvas by ID as fallback
    const canvasById = document.getElementById('chatActivityChart');
    console.log('🔍 Canvas by ID:', canvasById);
    if (canvasById) {
      chatActivityChart.value = canvasById;
    } else {
      return;
    }
  }

  try {
    // Destroy existing chart if it exists
    if (chatActivityChartInstance.value) {
      console.log('🗑️ Destroying existing chart');
      chatActivityChartInstance.value.destroy();
      chatActivityChartInstance.value = null;
    }

    // Fetch data first
    console.log('📊 Fetching chart data...');
    const dataFetched = await fetchChatActivityData();

    if (!dataFetched) {
      console.error('❌ Failed to fetch chart data');
      return;
    }

    console.log('📈 Chart data ready:', chartData.value);
    console.log('🎨 Canvas element ready:', chatActivityChart.value);
    console.log('📏 Canvas dimensions:', {
      width: chatActivityChart.value.clientWidth,
      height: chatActivityChart.value.clientHeight,
      offsetWidth: chatActivityChart.value.offsetWidth,
      offsetHeight: chatActivityChart.value.offsetHeight
    });

    // Ensure canvas has proper dimensions
    if (chatActivityChart.value.clientWidth === 0 || chatActivityChart.value.clientHeight === 0) {
      console.warn('⚠️ Canvas has zero dimensions, setting explicit size');
      chatActivityChart.value.style.width = '100%';
      chatActivityChart.value.style.height = '256px';
    }

    // Create new chart
    console.log('🎨 Creating Chart.js instance...');
    chatActivityChartInstance.value = new Chart(chatActivityChart.value, {
      type: 'line',
      data: chartData.value,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            mode: 'index',
            intersect: false,
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: 'rgb(59, 130, 246)',
            borderWidth: 1
          }
        },
        scales: {
          x: {
            display: true,
            grid: {
              display: false
            },
            ticks: {
              maxTicksLimit: 8,
              color: '#6B7280'
            }
          },
          y: {
            display: true,
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.1)'
            },
            ticks: {
              stepSize: 10,
              color: '#6B7280'
            }
          }
        },
        interaction: {
          mode: 'nearest',
          axis: 'x',
          intersect: false
        },
        elements: {
          point: {
            radius: 4,
            hoverRadius: 6
          }
        }
      }
    });

    console.log('✅ Chat activity chart created successfully:', chatActivityChartInstance.value);
    console.log('📊 Chart instance details:', {
      id: chatActivityChartInstance.value.id,
      canvas: chatActivityChartInstance.value.canvas,
      data: chatActivityChartInstance.value.data
    });

  } catch (error) {
    console.error('❌ Error creating chat activity chart:', error);
    console.error('Error details:', error.message);
    console.error('Stack trace:', error.stack);

    // Show error in UI
    notificationStore.error('Chart Error', 'Failed to create chart. Please check console for details.');
  }
};

const debouncedSearchRooms = debounce(() => {
  fetchRooms();
}, 300);

const toggleSelectAllRooms = () => {
  const roomsArray = Array.isArray(rooms.value) ? rooms.value : [];

  if (selectedRooms.value.length === roomsArray.length && roomsArray.length > 0) {
    selectedRooms.value = [];
  } else {
    selectedRooms.value = roomsArray.map(room => room.id);
  }
};

const getTypeBadgeClass = (type) => {
  return type === 'group'
    ? 'bg-blue-100 text-blue-800'
    : 'bg-blue-100 text-blue-800';
};

const getStatusBadgeClass = (isActive) => {
  return isActive
    ? 'bg-green-100 text-green-800'
    : 'bg-red-100 text-red-800';
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString();
};

const viewRoom = (room) => {
  notificationStore.info('Info', `View room: ${room.name}`);
};

const editRoom = (room) => {
  notificationStore.info('Info', `Edit room: ${room.name}`);
};

const deleteRoom = async (room) => {
  if (confirm(`Are you sure you want to delete room "${room.name}"?`)) {
    try {
      const result = await adminStore.deleteRoom(room.id);
      if (result.success) {
        notificationStore.success('Success', 'Room deleted successfully');
        fetchRooms();
      } else {
        notificationStore.error('Error', result.message);
      }
    } catch (error) {
      notificationStore.error('Error', 'Failed to delete room');
    }
  }
};

const bulkRoomAction = async (action) => {
  if (selectedRooms.value.length === 0) return;

  try {
    const result = await adminStore.bulkRoomAction(action, selectedRooms.value);
    if (result.success) {
      notificationStore.success('Success', `Rooms ${action}d successfully`);
      selectedRooms.value = [];
      fetchRooms();
    } else {
      notificationStore.error('Error', result.message);
    }
  } catch (error) {
    notificationStore.error('Error', `Failed to ${action} rooms`);
  }
};

const exportData = () => {
  notificationStore.info('Info', 'Export functionality will be implemented');
};

// Real-time listeners
const setupRealTimeListeners = () => {
  console.log('🔧 Setting up admin real-time listeners...');

  // Check if Echo is available
  if (!window.Echo) {
    console.warn('⚠️ Laravel Echo not available, skipping real-time listeners');
    return;
  }

  try {
    // Listen for new chat rooms
    window.Echo.channel('admin-chat-rooms')
      .listen('ChatRoomCreated', (e) => {
        console.log('🎉 Admin: New chat room created:', e);
        // Add new room to the list
        if (e.chatRoom && rooms.value) {
          rooms.value.unshift(e.chatRoom);
          updateStats();
          notificationStore.info('New Chat Room', `${e.chatRoom.name} has been created`);
        }
      })
      .listen('ChatRoomUpdated', (e) => {
        console.log('📝 Admin: Chat room updated:', e);
        // Update existing room in the list
        if (e.chatRoom && rooms.value) {
          const index = rooms.value.findIndex(room => room.id === e.chatRoom.id);
          if (index !== -1) {
            rooms.value[index] = e.chatRoom;
            updateStats();
          }
        }
      })
      .listen('ChatRoomDeleted', (e) => {
        console.log('🗑️ Admin: Chat room deleted:', e);
        // Remove room from the list
        if (e.chatRoomId && rooms.value) {
          rooms.value = rooms.value.filter(room => room.id !== e.chatRoomId);
          updateStats();
          notificationStore.info('Chat Room Deleted', 'A chat room has been deleted');
        }
      })
      .error((error) => {
        console.error('❌ Error on admin-chat-rooms channel:', error);
      });

    // Listen for new messages
    window.Echo.channel('admin-messages')
      .listen('MessageSent', (e) => {
        console.log('💬 Admin: New message sent:', e);
        // Update message count for the room
        if (e.message && rooms.value) {
          const room = rooms.value.find(r => r.id === e.message.chat_room_id);
          if (room) {
            room.messages_count = (room.messages_count || 0) + 1;
            room.updated_at = e.message.created_at;
            updateStats();
          }
        }
      })
      .error((error) => {
        console.error('❌ Error on admin-messages channel:', error);
      });

    console.log('✅ Admin real-time listeners setup complete');
  } catch (error) {
    console.error('❌ Error setting up real-time listeners:', error);
  }
};

const cleanupRealTimeListeners = () => {
  if (!window.Echo) return;

  try {
    window.Echo.leaveChannel('admin-chat-rooms');
    window.Echo.leaveChannel('admin-messages');
    console.log('✅ Admin real-time listeners cleaned up');
  } catch (error) {
    console.error('❌ Error cleaning up real-time listeners:', error);
  }
};

onMounted(async () => {
  console.log('🚀 AdminChats: Component mounted');
  console.log('📊 AdminChats: Initial state - rooms:', rooms.value, 'loading:', loading.value);
  fetchRooms();

  // Fetch messages when switching to messages tab
  if (activeTab.value === 'messages') {
    fetchMessages();
  }

  setupRealTimeListeners();

  // Create chart after DOM is ready with delay
  await nextTick();
  setTimeout(async () => {
    console.log('⏰ Creating chart after timeout...');
    await createChatActivityChart();
  }, 500);
});

// Watch for tab changes to fetch data
watch(activeTab, async (newTab) => {
  console.log('📋 Tab changed to:', newTab);
  if (newTab === 'messages' && messages.value.length === 0) {
    fetchMessages();
  }

  // Create chart when switching to reports tab
  if (newTab === 'reports') {
    await nextTick();
    setTimeout(async () => {
      console.log('📊 Creating chart for reports tab...');
      await createChatActivityChart();
    }, 300);
  }
});

onUnmounted(() => {
  console.log('🔥 AdminChats: Component unmounted');
  cleanupRealTimeListeners();

  // Cleanup chart
  if (chatActivityChartInstance.value) {
    chatActivityChartInstance.value.destroy();
    chatActivityChartInstance.value = null;
  }
});
</script>

<style scoped>
.gradient-text {
  background: linear-gradient(135deg, #3b82f6, #d946ef);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6, #d946ef);
  color: white;
  font-weight: 600;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.25);
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-primary:hover {
  box-shadow: 0 10px 25px -3px rgba(59, 130, 246, 0.3);
  transform: scale(1.05);
}

.btn-secondary {
  background: white;
  color: #475569;
  font-weight: 600;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-secondary:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
  transform: scale(1.05);
}

.card {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 1.5rem;
  transition: all 0.3s ease;
}

.card:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
</style>
