const fs = require('fs').promises;
const path = require('path');
const mongoose = require('mongoose');
const archiver = require('archiver');
const cron = require('node-cron');

class BackupService {
  constructor() {
    this.backupDir = path.join(__dirname, '../backups');
    this.ensureBackupDirectory();
  }

  // Ensure backup directory exists
  async ensureBackupDirectory() {
    try {
      await fs.mkdir(this.backupDir, { recursive: true });
    } catch (error) {
      console.error('Failed to create backup directory:', error);
    }
  }

  // Create database backup
  async createDatabaseBackup() {
    try {
      const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
      const backupFileName = `database-backup-${timestamp}.gz`;
      const backupPath = path.join(this.backupDir, backupFileName);

      console.log('Creating database backup...');

      // Use mongodump if available, otherwise manual backup
      if (process.env.MONGODUMP_PATH) {
        await this.createMongodumpBackup(backupPath);
      } else {
        await this.createManualBackup(backupPath);
      }

      console.log(`Database backup created: ${backupFileName}`);
      
      // Clean old backups (keep last 30 days)
      await this.cleanOldBackups(30);

      return {
        success: true,
        fileName: backupFileName,
        path: backupPath,
        size: await this.getFileSize(backupPath),
        timestamp: new Date().toISOString()
      };
    } catch (error) {
      console.error('Database backup failed:', error);
      return {
        success: false,
        error: error.message
      };
    }
  }

  // Create backup using mongodump
  async createMongodumpBackup(backupPath) {
    const { exec } = require('child_process');
    
    return new Promise((resolve, reject) => {
      const command = `${process.env.MONGODUMP_PATH} --uri="${process.env.MONGODB_URI}" --gzip --archive="${backupPath}"`;
      
      exec(command, (error, stdout, stderr) => {
        if (error) {
          reject(error);
        } else {
          resolve(stdout);
        }
      });
    });
  }

  // Create manual backup by exporting collections
  async createManualBackup(backupPath) {
    const collections = await mongoose.connection.db.collections();
    const backupData = {};

    for (const collection of collections) {
      const collectionName = collection.collectionName;
      console.log(`Backing up collection: ${collectionName}`);
      
      try {
        const documents = await collection.find({}).lean();
        backupData[collectionName] = documents;
      } catch (error) {
        console.error(`Failed to backup collection ${collectionName}:`, error);
        backupData[collectionName] = [];
      }
    }

    // Compress and save backup
    const zlib = require('zlib');
    const jsonData = JSON.stringify(backupData, null, 2);
    
    return new Promise((resolve, reject) => {
      const gzip = zlib.createGzip();
      const output = fs.createWriteStream(backupPath);
      
      gzip.on('error', reject);
      output.on('error', reject);
      output.on('finish', resolve);
      
      gzip.pipe(output);
      gzip.write(jsonData);
      gzip.end();
    });
  }

  // Create files backup
  async createFilesBackup() {
    try {
      const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
      const backupFileName = `files-backup-${timestamp}.zip`;
      const backupPath = path.join(this.backupDir, backupFileName);

      console.log('Creating files backup...');

      const archive = archiver('zip', { zlib: { level: 9 } });
      const output = fs.createWriteStream(backupPath);

      return new Promise((resolve, reject) => {
        output.on('close', () => {
          console.log(`Files backup created: ${backupFileName}`);
          resolve({
            success: true,
            fileName: backupFileName,
            path: backupPath,
            size: archive.pointer(),
            timestamp: new Date().toISOString()
          });
        });

        archive.on('error', reject);
        archive.pipe(output);

        // Add important directories to backup
        const directoriesToBackup = [
          'uploads',
          'logs',
          'config'
        ];

        for (const dir of directoriesToBackup) {
          const dirPath = path.join(__dirname, '..', dir);
          
          try {
            await fs.access(dirPath);
            archive.directory(dirPath, dir);
          } catch (error) {
            console.log(`Directory ${dir} not found, skipping...`);
          }
        }

        archive.finalize();
      });
    } catch (error) {
      console.error('Files backup failed:', error);
      return {
        success: false,
        error: error.message
      };
    }
  }

  // Restore database from backup
  async restoreDatabaseBackup(backupFileName) {
    try {
      const backupPath = path.join(this.backupDir, backupFileName);
      
      // Check if backup exists
      await fs.access(backupPath);
      
      console.log(`Restoring database from ${backupFileName}...`);

      if (backupFileName.endsWith('.gz')) {
        await this.restoreFromMongodump(backupPath);
      } else {
        await this.restoreFromManualBackup(backupPath);
      }

      console.log('Database restore completed successfully');
      
      return {
        success: true,
        message: 'Database restored successfully',
        restoredAt: new Date().toISOString()
      };
    } catch (error) {
      console.error('Database restore failed:', error);
      return {
        success: false,
        error: error.message
      };
    }
  }

  // Restore using mongorestore
  async restoreFromMongodump(backupPath) {
    const { exec } = require('child_process');
    
    return new Promise((resolve, reject) => {
      const command = `${process.env.MONGORESTORE_PATH} --uri="${process.env.MONGODB_URI}" --gzip --archive="${backupPath}" --drop`;
      
      exec(command, (error, stdout, stderr) => {
        if (error) {
          reject(error);
        } else {
          resolve(stdout);
        }
      });
    });
  }

  // Restore from manual backup
  async restoreFromManualBackup(backupPath) {
    const zlib = require('zlib');
    const data = await fs.readFile(backupPath);
    
    return new Promise((resolve, reject) => {
      const gunzip = zlib.createGunzip();
      
      gunzip.on('error', reject);
      gunzip.on('data', async (jsonData) => {
        try {
          const backupData = JSON.parse(jsonData.toString());
          
          for (const [collectionName, documents] of Object.entries(backupData)) {
            console.log(`Restoring collection: ${collectionName}`);
            
            try {
              const collection = mongoose.connection.db.collection(collectionName);
              await collection.deleteMany({});
              
              if (documents.length > 0) {
                await collection.insertMany(documents);
              }
            } catch (error) {
              console.error(`Failed to restore collection ${collectionName}:`, error);
            }
          }
          
          resolve();
        } catch (parseError) {
          reject(parseError);
        }
      });
      
      gunzip.write(data);
      gunzip.end();
    });
  }

  // Clean old backups
  async cleanOldBackups(daysToKeep = 30) {
    try {
      const files = await fs.readdir(this.backupDir);
      const cutoffDate = new Date(Date.now() - (daysToKeep * 24 * 60 * 60 * 1000));
      
      for (const file of files) {
        const filePath = path.join(this.backupDir, file);
        const stats = await fs.stat(filePath);
        
        if (stats.mtime < cutoffDate) {
          await fs.unlink(filePath);
          console.log(`Deleted old backup: ${file}`);
        }
      }
    } catch (error) {
      console.error('Failed to clean old backups:', error);
    }
  }

  // Get backup list
  async getBackupList() {
    try {
      const files = await fs.readdir(this.backupDir);
      const backupList = [];

      for (const file of files) {
        const filePath = path.join(this.backupDir, file);
        const stats = await fs.stat(filePath);
        
        backupList.push({
          fileName: file,
          path: filePath,
          size: stats.size,
          createdAt: stats.mtime,
          type: file.includes('database') ? 'database' : 'files'
        });
      }

      return backupList.sort((a, b) => b.createdAt - a.createdAt);
    } catch (error) {
      console.error('Failed to get backup list:', error);
      return [];
    }
  }

  // Get file size
  async getFileSize(filePath) {
    try {
      const stats = await fs.stat(filePath);
      return stats.size;
    } catch (error) {
      return 0;
    }
  }

  // Schedule automatic backups
  scheduleAutomaticBackups() {
    // Database backup every day at 2 AM
    cron.schedule('0 2 * * *', async () => {
      console.log('Starting scheduled database backup...');
      await this.createDatabaseBackup();
    });

    // Files backup every week on Sunday at 3 AM
    cron.schedule('0 3 * * 0', async () => {
      console.log('Starting scheduled files backup...');
      await this.createFilesBackup();
    });

    // Clean old backups every week on Sunday at 4 AM
    cron.schedule('0 4 * * 0', async () => {
      console.log('Starting scheduled cleanup of old backups...');
      await this.cleanOldBackups(30);
    });

    console.log('Automatic backup scheduling configured');
  }

  // Get backup statistics
  async getBackupStats() {
    try {
      const backupList = await this.getBackupList();
      const totalSize = backupList.reduce((sum, backup) => sum + backup.size, 0);
      
      const databaseBackups = backupList.filter(b => b.type === 'database');
      const filesBackups = backupList.filter(b => b.type === 'files');
      
      return {
        totalBackups: backupList.length,
        databaseBackups: databaseBackups.length,
        filesBackups: filesBackups.length,
        totalSize,
        totalSizeMB: (totalSize / (1024 * 1024)).toFixed(2),
        lastDatabaseBackup: databaseBackups[0]?.createdAt || null,
        lastFilesBackup: filesBackups[0]?.createdAt || null,
        backupDirectory: this.backupDir
      };
    } catch (error) {
      console.error('Failed to get backup stats:', error);
      return null;
    }
  }

  // Download backup
  async downloadBackup(backupFileName, res) {
    try {
      const backupPath = path.join(this.backupDir, backupFileName);
      await fs.access(backupPath);
      
      const stats = await fs.stat(backupPath);
      
      res.setHeader('Content-Type', 'application/octet-stream');
      res.setHeader('Content-Disposition', `attachment; filename="${backupFileName}"`);
      res.setHeader('Content-Length', stats.size);
      
      const fileStream = fs.createReadStream(backupPath);
      fileStream.pipe(res);
    } catch (error) {
      console.error('Failed to download backup:', error);
      res.status(404).json({
        success: false,
        message: 'Backup not found'
      });
    }
  }

  // Delete backup
  async deleteBackup(backupFileName) {
    try {
      const backupPath = path.join(this.backupDir, backupFileName);
      await fs.unlink(backupPath);
      
      console.log(`Backup deleted: ${backupFileName}`);
      
      return {
        success: true,
        message: 'Backup deleted successfully'
      };
    } catch (error) {
      console.error('Failed to delete backup:', error);
      return {
        success: false,
        error: error.message
      };
    }
  }
}

module.exports = new BackupService();
