<!-- Inside the <li> for each campaign in MyCampaignsView.vue -->
    <router-link :to="{ name: 'CampaignDetail', params: { id: campaign.id } }" class="view-details-link">
        View Details
      </router-link>

      <!-- Add some style for the link -->
      <style scoped>
      /* ... existing styles ... */
      .view-details-link {
        display: inline-block;
        margin-top: 10px;
        padding: 5px 10px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 3px;
        font-size: 0.9em;
      }
      .view-details-link:hover {
        background-color: #0056b3;
      }
      </style>
