/*
   Copyright 2011 Robert Spencer

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
 */

package com.networkmanagerapp;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;

import org.apache.http.HttpHost;
import org.apache.http.HttpResponse;
import org.apache.http.auth.AuthScope;
import org.apache.http.auth.UsernamePasswordCredentials;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.mime.MultipartEntity;
import org.apache.http.entity.mime.content.FileBody;
import org.apache.http.impl.client.DefaultHttpClient;

import android.app.IntentService;
import android.app.Notification;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.preference.PreferenceManager;
import android.util.Log;

/**
 * This class is a subclass of IntentService to upload new settings to the router in the background
 * @author rjs07u
 */
public class SettingBackgroundUploader extends IntentService {
	private NotificationManager mNM;
	String configFilePath;
	String key;
	String value;
	FileOutputStream fos;

	/**
	 * Constructor calls the superclass constructor with a name. Used for debugging purposes only
	 */
	public SettingBackgroundUploader() {
		super("SETTING UPLOADER");
	}
	
	/**
	 * Create a notification to inform the user of progress in the system notification area
	 */
	private void showNotification(){
		CharSequence text = getText(R.string.upload_service_started);
		Notification notification = new Notification(R.drawable.ic_stat_networkman, text, System.currentTimeMillis());
		PendingIntent contentIntent = PendingIntent.getActivity(this, 0, new Intent(this, NetworkManagerMainActivity.class), 0);
		notification.setLatestEventInfo(this, text, text, contentIntent);
		mNM.notify(R.string.upload_service_started, notification);
	}

	/**
	 * Performs the file upload in the background
	 * @throws FileNotFoundException, IOException, both caught internally. 
	 * @param arg0 The intent to run in the background.
	 */
	@Override
	protected void onHandleIntent(Intent arg0) {
		configFilePath = arg0.getStringExtra("CONFIG_FILE_PATH");
		key = arg0.getStringExtra("KEY");
		value = arg0.getStringExtra("VALUE");
		mNM = (NotificationManager)getSystemService(NOTIFICATION_SERVICE);
		showNotification();
		try {
			fos = NetworkManagerMainActivity.getInstance().openFileOutput(
					this.configFilePath, Context.MODE_PRIVATE);
			String nameLine = "Name" + " = " + "\"" + this.key + "\"\n";
			String valueLine = "Value" + " = " + "\"" + this.value + "\"";
			fos.write(nameLine.getBytes());
			fos.write(valueLine.getBytes());
			fos.close();

			String password = PreferenceManager.getDefaultSharedPreferences(this).getString("password_preference", "");
			File f = new File(NetworkManagerMainActivity.getInstance().getFilesDir() + "/" + this.configFilePath);
			HttpHost targetHost = new HttpHost(PreferenceManager.getDefaultSharedPreferences(this).getString("ip_preference", "192.168.1.1"), 1080, "http");
			DefaultHttpClient client = new DefaultHttpClient();
			client.getCredentialsProvider().setCredentials(
			        new AuthScope(targetHost.getHostName(), targetHost.getPort()), 
			        new UsernamePasswordCredentials("root", password));
			HttpPost httpPost = new HttpPost("http://"+ PreferenceManager.getDefaultSharedPreferences(NetworkManagerMainActivity.getInstance()).getString("ip_preference", "192.168.1.1") +":1080/cgi-bin/upload.php");
			MultipartEntity entity = new MultipartEntity();
			entity.addPart("file", new FileBody(f));
			httpPost.setEntity(entity);
			HttpResponse response = client.execute(httpPost);
			Log.d("upload", response.getStatusLine().toString());
		} catch (FileNotFoundException e) {
			Log.e("NETWORK_MANAGER_XU_FNFE", e.getLocalizedMessage());
		} catch (IOException e) {
			Log.e("NETWORK_MANAGER_XU_IOE", e.getLocalizedMessage());
		}finally{
			mNM.cancel(R.string.upload_service_started);
			stopSelf();
		}
	}
}
